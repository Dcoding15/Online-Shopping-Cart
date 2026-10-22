<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('buyer can view products catalog', function () {
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Sample Laptop Test',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 5,
        'product_price' => 799.99,
        'product_type' => 'Laptops',
        'product_category' => 'Electronics',
        'product_description' => 'A powerful laptop for development',
    ]);

    $response = $this->get(route('products.index'));

    $response->assertStatus(200);
    $response->assertSee('Sample Laptop Test');
    $response->assertSee('₹799.99');
});

test('buyer can search products by keyword', function () {
    $seller = User::factory()->seller()->create();

    Product::create([
        'product_name' => 'Searchable Drone Alpha',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 3,
        'product_price' => 450.00,
        'product_type' => 'Digital Cameras',
        'product_category' => 'Cameras & Optics',
        'product_description' => 'High altitude aerial quadcopter',
    ]);

    $response = $this->get(route('products.index', ['search' => 'Drone Alpha']));

    $response->assertStatus(200);
    $response->assertSee('Searchable Drone Alpha');
});

test('buyer can filter products by category and type', function () {
    $seller = User::factory()->seller()->create();

    Product::create([
        'product_name' => 'Category Filtered Headphones',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 10,
        'product_price' => 120.00,
        'product_type' => 'Headphones',
        'product_category' => 'Electronics',
    ]);

    $response = $this->get(route('products.index', [
        'category' => 'Electronics',
        'type' => 'Headphones',
    ]));

    $response->assertStatus(200);
    $response->assertSee('Category Filtered Headphones');
});

test('buyer can filter in stock products', function () {
    $seller = User::factory()->seller()->create();

    Product::create([
        'product_name' => 'Out of Stock Coffee',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 0,
        'product_price' => 15.00,
        'product_type' => 'Coffee Beans',
        'product_category' => 'Food, Beverages & Tobacco',
    ]);

    Product::create([
        'product_name' => 'In Stock Coffee Beans',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 20,
        'product_price' => 25.00,
        'product_type' => 'Coffee Beans',
        'product_category' => 'Food, Beverages & Tobacco',
    ]);

    $response = $this->get(route('products.index', [
        'category' => 'Food, Beverages & Tobacco',
        'in_stock' => '1',
    ]));

    $response->assertStatus(200);
    $response->assertSee('In Stock Coffee Beans');
    $response->assertDontSee('Out of Stock Coffee');
});

test('buyer can view single product details', function () {
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Detailed Gaming Chair',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 8,
        'product_price' => 250.00,
        'product_type' => 'Office Chairs',
        'product_category' => 'Furniture',
        'product_description' => 'Ergonomic lumbar support chair',
    ]);

    $response = $this->get(route('products.show', $product->product_id));

    $response->assertStatus(200);
    $response->assertSee('Detailed Gaming Chair');
    $response->assertSee('Ergonomic lumbar support chair');
    $response->assertSee('In Stock (8 units available)');
    $response->assertSee('Add to Cart');
});

test('out of stock product shows out of stock notice', function () {
    $seller = User::factory()->seller()->create();

    $product = Product::create([
        'product_name' => 'Sold Out Smartphone',
        'user_id' => $seller->user_id,
        'product_available_quantity' => 0,
        'product_price' => 999.00,
        'product_type' => 'Smartphones',
        'product_category' => 'Electronics',
    ]);

    $response = $this->get(route('products.show', $product->product_id));

    $response->assertStatus(200);
    $response->assertSee('Sold Out Smartphone');
    $response->assertSee('Out of Stock');
    $response->assertSee('This product is currently out of stock');
});
