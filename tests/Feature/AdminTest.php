<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('admin can view admin dashboard with key metrics', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Admin Control Panel');
    $response->assertSee('System Key Metrics');
});

test('buyer and seller are blocked from admin area with 403', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $this->actingAs($buyer)->get(route('admin.dashboard'))->assertStatus(403);
    $this->actingAs($seller)->get(route('admin.dashboard'))->assertStatus(403);
});

test('guest is redirected to login from admin routes', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('admin can view and filter user list', function () {
    $admin = User::factory()->admin()->create();
    $seller = User::factory()->seller()->create(['user_name' => 'Target Seller AdminTest']);

    $response = $this->actingAs($admin)->get(route('admin.users.index', ['type' => 'seller', 'search' => 'AdminTest']));

    $response->assertStatus(200);
    $response->assertSee('Target Seller AdminTest');
});

test('admin can update user role and wallet amount', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create([
        'user_type' => 'buyer',
        'user_wallet_amount' => 50.00,
    ]);

    $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->user_id), [
        'user_name' => 'Promoted User',
        'phone_no' => $targetUser->phone_no,
        'user_type' => 'seller',
        'user_wallet_amount' => 500.00,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('success');

    $targetUser->refresh();
    expect($targetUser->user_name)->toBe('Promoted User')
        ->and($targetUser->user_type)->toBe('seller')
        ->and((float) $targetUser->user_wallet_amount)->toBe(500.0);
});

test('admin cannot delete their own account', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin->user_id));

    $response->assertRedirect(route('admin.users.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['user_id' => $admin->user_id]);
});

test('admin can view platform orders and detailed invoice', function () {
    $admin = User::factory()->admin()->create();
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $order = Order::create([
        'user_id' => $buyer->user_id,
        'order_total_amount' => 200.00,
        'order_status' => 'paid',
        'payment_confirmed' => true,
        'shipping_address' => '500 Admin View Way',
        'created_at' => now(),
    ]);

    OrderItem::create([
        'order_id' => $order->order_id,
        'seller_id' => $seller->user_id,
        'product_name' => 'Admin Monitored Laptop',
        'quantity' => 1,
        'unit_price' => 200.00,
        'subtotal' => 200.00,
    ]);

    // 1. Orders list
    $responseList = $this->actingAs($admin)->get(route('admin.orders.index'));
    $responseList->assertStatus(200);
    $responseList->assertSee('₹200.00');

    // 2. Order invoice
    $responseInvoice = $this->actingAs($admin)->get(route('admin.orders.show', $order->order_id));
    $responseInvoice->assertStatus(200);
    $responseInvoice->assertSee('Admin Monitored Laptop');
    $responseInvoice->assertSee('500 Admin View Way');
});

test('admin can delete a product', function () {
    $admin = User::factory()->admin()->create();
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Product To Remove',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 5,
        'product_price' => 30.00,
        'product_type' => 'Shoes',
        'product_category' => 'Apparel & Accessories',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product->product_id));

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('products', ['product_id' => $product->product_id]);
});
