<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('seller index displays list of sellers', function () {
    $admin = User::factory()->admin()->create();
    $seller = User::factory()->seller()->create([
        'user_name' => 'Test Seller One',
    ]);
    $page = intdiv(
        User::query()
            ->where('user_type', 'seller')
            ->where('user_id', '<', $seller->user_id)
            ->count(),
        15
    ) + 1;

    $response = $this->actingAs($admin)->get(route('seller.index', ['page' => $page]));

    $response->assertStatus(200);
    $response->assertSee('Test Seller One');
});

test('can create a seller', function () {
    $admin = User::factory()->admin()->create();
    $phone = '999'.rand(1000000, 9999999);

    $response = $this->actingAs($admin)->post(route('seller.store'), [
        'user_name' => 'New Seller Test',
        'phone_no' => $phone,
        'user_addr' => '123 Market Street',
        'date_of_birth' => '1990-05-15',
        'user_wallet_amount' => '150.00',
        'password' => 'secret123',
    ]);

    $response->assertRedirect(route('seller.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'user_name' => 'New Seller Test',
        'phone_no' => $phone,
        'user_type' => 'seller',
    ]);
});

test('can update a seller', function () {
    $admin = User::factory()->admin()->create();
    $seller = User::factory()->create([
        'user_type' => 'seller',
        'user_name' => 'Original Seller',
    ]);

    $response = $this->actingAs($admin)->put(route('seller.update', $seller->user_id), [
        'user_name' => 'Updated Seller Name',
        'phone_no' => $seller->phone_no,
        'user_addr' => '456 Updated Ave',
        'date_of_birth' => '1992-01-01',
        'user_wallet_amount' => '300.50',
    ]);

    $response->assertRedirect(route('seller.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'user_id' => $seller->user_id,
        'user_name' => 'Updated Seller Name',
        'user_addr' => '456 Updated Ave',
    ]);
});

test('prevent seller deletion when seller owns products', function () {
    $admin = User::factory()->admin()->create();
    $seller = User::factory()->create([
        'user_type' => 'seller',
    ]);

    $product = Product::create([
        'product_name' => 'Protected Product',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 29.99,
        'product_type' => 'Pet Food',
        'product_category' => 'Animals & Pet Supplies',
    ]);

    $response = $this->actingAs($admin)->delete(route('seller.destroy', $seller->user_id));

    $response->assertRedirect(route('seller.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', [
        'user_id' => $seller->user_id,
    ]);

    $this->assertDatabaseHas('products', [
        'product_id' => $product->product_id,
    ]);
});

test('seller without products can be deleted', function () {
    $admin = User::factory()->admin()->create();
    $seller = User::factory()->create([
        'user_type' => 'seller',
    ]);

    $response = $this->actingAs($admin)->delete(route('seller.destroy', $seller->user_id));

    $response->assertRedirect(route('seller.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', [
        'user_id' => $seller->user_id,
    ]);
});

test('seller management is unavailable to guests and non-admin users', function () {
    $this->get(route('seller.index'))->assertRedirect(route('login'));

    $buyer = User::factory()->create(['user_type' => 'buyer']);

    $this->actingAs($buyer)
        ->post(route('seller.store'), [])
        ->assertForbidden();
});

test('seller product pages use the current categories and rupee pricing', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('seller.products.create'))
        ->assertOk()
        ->assertSee('Price (₹)');

    $product = Product::create([
        'product_name' => 'Seller Product Test',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 3,
        'product_price' => 49.99,
        'product_type' => 'Laptops',
        'product_category' => 'Electronics',
    ]);

    $this->get(route('seller.products.edit', $product->product_id))
        ->assertOk()
        ->assertSee('value="Electronics" selected', false)
        ->assertSee('Price (₹)');

    $this->get(route('seller.products'))
        ->assertOk()
        ->assertSee('₹49.99');
});
