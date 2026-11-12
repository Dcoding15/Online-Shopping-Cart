<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

function configureRazorpayForTests(): void
{
    config([
        'services.razorpay.key_id' => 'rzp_test_example',
        'services.razorpay.key_secret' => 'test_secret',
        'services.razorpay.api_url' => 'https://api.razorpay.com/v1',
    ]);
}

test('checkout offers card and UPI payments without a wallet', function () {
    $buyer = User::factory()->buyer()->create([
        'user_addr' => '789 Checkout Blvd',
    ]);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Checkout Item 1',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 5,
        'product_price' => 75.00,
        'product_type' => 'Clothing',
        'product_category' => 'Apparel & Accessories',
    ]);

    Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($buyer)->get(route('checkout'));

    $response->assertOk();
    $response->assertSee('Checkout Item 1');
    $response->assertSee('₹150.00');
    $response->assertSee('card');
    $response->assertSee('UPI');
    $response->assertSee('Razorpay Checkout');
    $response->assertDontSee('Wallet');
});

test('cannot view checkout when cart is empty', function () {
    $buyer = User::factory()->buyer()->create();

    $response = $this->actingAs($buyer)->get(route('checkout'));

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('error');
});

test('online checkout creates and verifies a Razorpay payment before completing the order', function () {
    configureRazorpayForTests();

    $buyer = User::factory()->buyer()->create([
        'user_addr' => '100 Main St',
    ]);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'High End Headphones',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 80.00,
        'product_type' => 'Headphones',
        'product_category' => 'Electronics',
    ]);

    Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 2,
    ]);
    $buyer->updateCartQuantity();

    Http::fake([
        'api.razorpay.com/v1/orders' => Http::response([
            'id' => 'order_test_123',
            'amount' => 16000,
            'currency' => 'INR',
        ]),
        'api.razorpay.com/v1/payments/pay_test_123' => Http::response([
            'id' => 'pay_test_123',
            'order_id' => 'order_test_123',
            'amount' => 16000,
            'currency' => 'INR',
            'status' => 'captured',
            'method' => 'upi',
        ]),
    ]);

    $startResponse = $this->actingAs($buyer)->postJson(route('orders.store'), [
        'shipping_address' => '200 Delivery Ave',
    ]);
    $startResponse->assertOk();
    $startResponse->assertJsonPath('razorpay_order_id', 'order_test_123');
    $startResponse->assertJsonPath('amount', 16000);

    $order = Order::where('user_id', $buyer->user_id)->firstOrFail();
    expect($order->order_status)->toBe('pending')
        ->and($order->payment_confirmed)->toBeFalse();

    $signature = hash_hmac('sha256', 'order_test_123|pay_test_123', 'test_secret');
    $verifyResponse = $this->postJson(route('orders.verify'), [
        'razorpay_order_id' => 'order_test_123',
        'razorpay_payment_id' => 'pay_test_123',
        'razorpay_signature' => $signature,
    ]);

    $verifyResponse->assertOk();
    $verifyResponse->assertJsonPath('redirect_url', route('orders.show', $order->order_id));

    $order->refresh();
    expect($order->order_total_amount)->toBe('160.00')
        ->and($order->order_status)->toBe('paid')
        ->and($order->payment_confirmed)->toBeTrue()
        ->and($order->payment_provider)->toBe('razorpay')
        ->and($order->payment_method)->toBe('upi')
        ->and($order->shipping_address)->toBe('200 Delivery Ave');

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->order_id,
        'product_id' => $product->product_id,
        'seller_id' => $seller->user_id,
        'product_name' => 'High End Headphones',
        'quantity' => 2,
        'unit_price' => 80.00,
        'subtotal' => 160.00,
    ]);

    $buyer->refresh();
    expect($buyer->user_cart_quantity)->toBe(0);

    $product->refresh();
    expect($product->product_available_quantity)->toBe(8);

    $this->assertDatabaseMissing('cart', ['user_id' => $buyer->user_id]);
});

test('invalid Razorpay signatures do not fulfill an order', function () {
    configureRazorpayForTests();

    $buyer = User::factory()->buyer()->create();
    $seller = User::factory()->seller()->create();
    $product = Product::create([
        'product_name' => 'Signature Test Product',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 4,
        'product_price' => 50.00,
        'product_type' => 'Books',
        'product_category' => 'Media',
    ]);
    Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 1,
    ]);

    Http::fake([
        'api.razorpay.com/v1/orders' => Http::response([
            'id' => 'order_bad_signature',
            'amount' => 5000,
            'currency' => 'INR',
        ]),
    ]);

    $this->actingAs($buyer)->postJson(route('orders.store'), [
        'shipping_address' => 'Test Address',
    ])->assertOk();

    $order = Order::where('user_id', $buyer->user_id)->firstOrFail();
    $this->postJson(route('orders.verify'), [
        'razorpay_order_id' => 'order_bad_signature',
        'razorpay_payment_id' => 'pay_invalid',
        'razorpay_signature' => 'invalid',
    ])->assertUnprocessable();

    $order->refresh();
    expect($order->order_status)->toBe('pending')
        ->and($order->payment_confirmed)->toBeFalse();
    expect($product->fresh()->product_available_quantity)->toBe(4);
});

test('captured payment is refunded if stock runs out before verification', function () {
    configureRazorpayForTests();

    $buyer = User::factory()->buyer()->create();
    $seller = User::factory()->seller()->create();
    $product = Product::create([
        'product_name' => 'Last Unit Product',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 1,
        'product_price' => 40.00,
        'product_type' => 'Books',
        'product_category' => 'Media',
    ]);
    Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 1,
    ]);

    Http::fake([
        'api.razorpay.com/v1/orders' => Http::response([
            'id' => 'order_stock_race',
            'amount' => 4000,
            'currency' => 'INR',
        ]),
        'api.razorpay.com/v1/payments/pay_stock_race' => Http::response([
            'id' => 'pay_stock_race',
            'order_id' => 'order_stock_race',
            'amount' => 4000,
            'currency' => 'INR',
            'status' => 'captured',
            'method' => 'card',
        ]),
        'api.razorpay.com/v1/payments/pay_stock_race/refund' => Http::response([
            'id' => 'rfnd_stock_race',
            'status' => 'processed',
        ]),
    ]);

    $this->actingAs($buyer)->postJson(route('orders.store'), [
        'shipping_address' => 'Test Address',
    ])->assertOk();

    $order = Order::where('user_id', $buyer->user_id)->firstOrFail();
    $product->update(['product_available_quantity' => 0]);

    $signature = hash_hmac('sha256', 'order_stock_race|pay_stock_race', 'test_secret');
    $response = $this->postJson(route('orders.verify'), [
        'razorpay_order_id' => 'order_stock_race',
        'razorpay_payment_id' => 'pay_stock_race',
        'razorpay_signature' => $signature,
    ]);

    $response->assertOk();
    $response->assertJsonPath('redirect_url', route('orders.show', $order->order_id));
    $order->refresh();
    expect($order->order_status)->toBe('refunded')
        ->and($order->payment_confirmed)->toBeFalse()
        ->and($order->razorpay_refund_id)->toBe('rfnd_stock_race');

    $this->assertDatabaseHas('cart', [
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
    ]);
});

test('checkout reports missing Razorpay credentials without creating an order', function () {
    config([
        'services.razorpay.key_id' => null,
        'services.razorpay.key_secret' => null,
    ]);

    $buyer = User::factory()->buyer()->create();
    $seller = User::factory()->seller()->create();
    $product = Product::create([
        'product_name' => 'Unconfigured Gateway Product',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 2,
        'product_price' => 15.00,
        'product_type' => 'Books',
        'product_category' => 'Media',
    ]);
    Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 1,
    ]);

    $this->actingAs($buyer)->postJson(route('orders.store'), [
        'shipping_address' => 'Test Address',
    ])->assertStatus(503);

    $this->assertDatabaseMissing('orders', ['user_id' => $buyer->user_id]);
});

test('buyer can view order invoice', function () {
    $buyer = User::factory()->buyer()->create();

    $order = Order::create([
        'user_id' => $buyer->user_id,
        'order_total_amount' => 120.00,
        'order_status' => 'paid',
        'payment_confirmed' => true,
        'payment_provider' => 'razorpay',
        'payment_method' => 'card',
        'shipping_address' => '321 Elm Street',
        'created_at' => now(),
    ]);

    OrderItem::create([
        'order_id' => $order->order_id,
        'product_name' => 'Invoice Product Alpha',
        'quantity' => 3,
        'unit_price' => 40.00,
        'subtotal' => 120.00,
    ]);

    $response = $this->actingAs($buyer)->get(route('orders.show', $order->order_id));

    $response->assertOk();
    $response->assertSee('Order Invoice #'.$order->order_id);
    $response->assertSee('Invoice Product Alpha');
    $response->assertSee('₹120.00');
    $response->assertSee('Paid via Razorpay (Card)');
});

test('buyer cannot view another buyers order invoice', function () {
    $buyer1 = User::factory()->buyer()->create();
    $buyer2 = User::factory()->buyer()->create();

    $order = Order::create([
        'user_id' => $buyer1->user_id,
        'order_total_amount' => 50.00,
        'order_status' => 'paid',
        'payment_confirmed' => true,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($buyer2)->get(route('orders.show', $order->order_id));

    $response->assertNotFound();
});
