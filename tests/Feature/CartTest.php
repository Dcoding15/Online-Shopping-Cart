<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('buyer can add an in-stock product to cart', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Cart Test Product',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 50.00,
        'product_type' => 'Smartphones',
        'product_category' => 'Electronics',
    ]);

    $response = $this->actingAs($buyer)->post(route('cart.store'), [
        'product_id' => $product->product_id,
        'quantity' => 2,
    ]);

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cart', [
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 2,
    ]);

    // Check user_cart_quantity was synchronized
    $buyer->refresh();
    expect($buyer->user_cart_quantity)->toBe(2);
});

test('adding product beyond available stock is blocked', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Limited Stock Watch',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 3,
        'product_price' => 150.00,
        'product_type' => 'Smartwatches',
        'product_category' => 'Electronics',
    ]);

    $response = $this->actingAs($buyer)->from(route('products.show', $product->product_id))->post(route('cart.store'), [
        'product_id' => $product->product_id,
        'quantity' => 5,
    ]);

    $response->assertRedirect(route('products.show', $product->product_id));
    $response->assertSessionHas('error');

    $this->assertDatabaseMissing('cart', [
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
    ]);
});

test('adding out of stock product to cart is blocked', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Zero Stock Item',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 0,
        'product_price' => 99.00,
        'product_type' => 'Headphones',
        'product_category' => 'Electronics',
    ]);

    $response = $this->actingAs($buyer)->from(route('products.show', $product->product_id))->post(route('cart.store'), [
        'product_id' => $product->product_id,
        'quantity' => 1,
    ]);

    $response->assertRedirect(route('products.show', $product->product_id));
    $response->assertSessionHas('error');

    $this->assertDatabaseMissing('cart', [
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
    ]);
});

test('adding same product increments existing cart item quantity', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Book Test',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 20.00,
        'product_type' => 'Books',
        'product_category' => 'Media',
    ]);

    $this->actingAs($buyer)->post(route('cart.store'), [
        'product_id' => $product->product_id,
        'quantity' => 2,
    ]);

    $this->actingAs($buyer)->post(route('cart.store'), [
        'product_id' => $product->product_id,
        'quantity' => 3,
    ]);

    $this->assertDatabaseHas('cart', [
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 5,
    ]);

    $buyer->refresh();
    expect($buyer->user_cart_quantity)->toBe(5);
});

test('buyer can view cart and totals', function () {
    $buyer = User::factory()->create([
        'user_type' => 'buyer',
        'user_wallet_amount' => 500.00,
    ]);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Wireless Keyboard',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 15,
        'product_price' => 45.00,
        'product_type' => 'Laptops',
        'product_category' => 'Electronics',
    ]);

    Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 2,
    ]);

    $buyer->updateCartQuantity();

    $response = $this->actingAs($buyer)->get(route('cart.index'));

    $response->assertStatus(200);
    $response->assertSee('Wireless Keyboard');
    $response->assertSee('₹90.00'); // 2 * 45
    $response->assertSee('₹500.00'); // Wallet
});

test('buyer can update cart item quantity', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Desk Lamp',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 30.00,
        'product_type' => 'Sofas',
        'product_category' => 'Furniture',
    ]);

    $cartItem = Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 2,
    ]);

    $response = $this->actingAs($buyer)->put(route('cart.update', $cartItem->cart_id), [
        'quantity' => 4,
    ]);

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cart', [
        'cart_id' => $cartItem->cart_id,
        'quantity' => 4,
    ]);

    $buyer->refresh();
    expect($buyer->user_cart_quantity)->toBe(4);
});

test('buyer can remove item from cart', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Coffee Mug',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 12.00,
        'product_type' => 'Coffee Beans',
        'product_category' => 'Food, Beverages & Tobacco',
    ]);

    $cartItem = Cart::create([
        'user_id' => $buyer->user_id,
        'product_id' => $product->product_id,
        'quantity' => 1,
    ]);

    $buyer->updateCartQuantity();

    $response = $this->actingAs($buyer)->delete(route('cart.destroy', $cartItem->cart_id));

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('cart', [
        'cart_id' => $cartItem->cart_id,
    ]);

    $buyer->refresh();
    expect($buyer->user_cart_quantity)->toBe(0);
});

test('buyer can clear entire cart', function () {
    $buyer = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $p1 = Product::create([
        'product_name' => 'Item 1',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 10.00,
        'product_type' => 'Shoes',
        'product_category' => 'Apparel & Accessories',
    ]);

    $p2 = Product::create([
        'product_name' => 'Item 2',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 20.00,
        'product_type' => 'Clothing',
        'product_category' => 'Apparel & Accessories',
    ]);

    Cart::create(['user_id' => $buyer->user_id, 'product_id' => $p1->product_id, 'quantity' => 2]);
    Cart::create(['user_id' => $buyer->user_id, 'product_id' => $p2->product_id, 'quantity' => 3]);

    $buyer->updateCartQuantity();
    expect($buyer->user_cart_quantity)->toBe(5);

    $response = $this->actingAs($buyer)->delete(route('cart.clear'));

    $response->assertRedirect(route('cart.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseCount('cart', 0);

    $buyer->refresh();
    expect($buyer->user_cart_quantity)->toBe(0);
});

test('buyer cannot manipulate another buyers cart item', function () {
    $buyer1 = User::factory()->create(['user_type' => 'buyer']);
    $buyer2 = User::factory()->create(['user_type' => 'buyer']);
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Private Cart Item',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 50.00,
        'product_type' => 'Smartphones',
        'product_category' => 'Electronics',
    ]);

    $itemBuyer1 = Cart::create([
        'user_id' => $buyer1->user_id,
        'product_id' => $product->product_id,
        'quantity' => 1,
    ]);

    // Buyer 2 tries to delete Buyer 1's cart item
    $response = $this->actingAs($buyer2)->delete(route('cart.destroy', $itemBuyer1->cart_id));

    $response->assertStatus(404);

    $this->assertDatabaseHas('cart', [
        'cart_id' => $itemBuyer1->cart_id,
    ]);
});
