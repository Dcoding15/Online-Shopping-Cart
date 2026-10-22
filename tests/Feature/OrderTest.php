<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('buyer can view checkout page with order summary and wallet balance', function () {
    $buyer = User::factory()->create([
        'user_type' => 'buyer',
        'user_wallet_amount' => 300.00,
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

    $response->assertStatus(200);
    $response->assertSee('Checkout Item 1');
    $response->assertSee('₹150.00'); // total price (2 * 75)
    $response->assertSee('₹300.00'); // wallet amount
    $response->assertSee('789 Checkout Blvd');
});

test('cannot view checkout when cart is empty', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);

    $response = $this->actingAs($buyer)->get(route('checkout'));

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('error');
});

test('order placement deducts wallet, credits seller, decrements stock, and creates order records', function () {
    $buyer = User::factory()->create([
        'user_type' => 'buyer',
        'user_wallet_amount' => 500.00,
        'user_addr' => '100 Main St',
    ]);

    $seller = User::factory()->seller()->create([
        'user_wallet_amount' => 50.00,
    ]);

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
        'quantity' => 2, // Total = 160.00
    ]);
    $buyer->updateCartQuantity();

    $response = $this->actingAs($buyer)->post(route('orders.store'), [
        'shipping_address' => '200 Delivery Ave',
    ]);

    // Check redirect to order confirmation/invoice
    $order = Order::where('user_id', $buyer->user_id)->first();
    expect($order)->not->toBeNull();

    $response->assertRedirect(route('orders.show', $order->order_id));
    $response->assertSessionHas('success');

    // 1. Order details verified
    expect($order->order_total_amount)->toBe('160.00')
        ->and($order->order_status)->toBe('paid')
        ->and($order->payment_confirmed)->toBeTrue()
        ->and($order->shipping_address)->toBe('200 Delivery Ave');

    // 2. OrderItem verified
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->order_id,
        'product_id' => $product->product_id,
        'seller_id' => $seller->user_id,
        'product_name' => 'High End Headphones',
        'quantity' => 2,
        'unit_price' => 80.00,
        'subtotal' => 160.00,
    ]);

    // 3. Buyer wallet deduction (500 - 160 = 340) & payment confirmation
    $buyer->refresh();
    expect((float) $buyer->user_wallet_amount)->toBe(340.0)
        ->and($buyer->userpayment_confirmation)->toBeTrue()
        ->and($buyer->user_cart_quantity)->toBe(0);

    // 4. Seller wallet credit (50 + 160 = 210)
    $seller->refresh();
    expect((float) $seller->user_wallet_amount)->toBe(210.0);

    // 5. Product stock decremented (10 - 2 = 8)
    $product->refresh();
    expect($product->product_available_quantity)->toBe(8);

    // 6. Cart emptied
    $this->assertDatabaseMissing('cart', [
        'user_id' => $buyer->user_id,
    ]);
});

test('order placement fails when wallet has insufficient funds', function () {
    $buyer = User::factory()->create([
        'user_type' => 'buyer',
        'user_wallet_amount' => 50.00, // Insufficient for 100.00 order
    ]);

    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Expensive Shoes',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 5,
        'product_price' => 100.00,
        'product_type' => 'Shoes',
        'product_category' => 'Apparel & Accessories',
    ]);

    Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($buyer)->from(route('checkout'))->post(route('orders.store'), [
        'shipping_address' => 'Sample Addr',
    ]);

    $response->assertRedirect(route('checkout'));
    $response->assertSessionHas('error');

    $this->assertDatabaseMissing('orders', [
        'user_id' => $buyer->user_id,
    ]);

    // Wallet untouched
    $buyer->refresh();
    expect((float) $buyer->user_wallet_amount)->toBe(50.0);

    // Stock untouched
    $product->refresh();
    expect($product->product_available_quantity)->toBe(5);
});

test('buyer can view order invoice', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);

    $order = Order::create([
        'user_id' => $buyer->user_id,
        'order_total_amount' => 120.00,
        'order_status' => 'paid',
        'payment_confirmed' => true,
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

    $response->assertStatus(200);
    $response->assertSee('Order Invoice #'.$order->order_id);
    $response->assertSee('Invoice Product Alpha');
    $response->assertSee('₹120.00');
    $response->assertSee('Paid via User Wallet');
});

test('buyer cannot view another buyers order invoice', function () {
    $buyer1 = User::factory()->create(['user_type' => 'buyer']);
    $buyer2 = User::factory()->create(['user_type' => 'buyer']);

    $order = Order::create([
        'user_id' => $buyer1->user_id,
        'order_total_amount' => 50.00,
        'order_status' => 'paid',
        'payment_confirmed' => true,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($buyer2)->get(route('orders.show', $order->order_id));

    $response->assertStatus(404);
});
