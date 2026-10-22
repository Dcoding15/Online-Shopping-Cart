<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:routes', function () {
    $descriptions = [
        'admin.dashboard' => 'Show marketplace totals and admin metrics.',
        'admin.users.index' => 'List and filter platform users.',
        'admin.users.edit' => 'Show the form for editing a user.',
        'admin.users.update' => 'Update a user role, details, or wallet.',
        'admin.users.destroy' => 'Delete a user when deletion is allowed.',
        'admin.sellers.index' => 'List platform sellers.',
        'admin.buyers.index' => 'List platform buyers.',
        'admin.products.index' => 'List and filter all marketplace products.',
        'admin.products.destroy' => 'Remove a product as an administrator.',
        'admin.orders.index' => 'List orders across the marketplace.',
        'admin.orders.show' => 'Show an order invoice to an administrator.',
        'cart.index' => 'Show the signed-in buyer’s cart and totals.',
        'cart.store' => 'Add a product to the buyer’s cart.',
        'cart.update' => 'Change a cart item quantity.',
        'cart.destroy' => 'Remove an item from the buyer’s cart.',
        'cart.clear' => 'Remove all items from the buyer’s cart.',
        'checkout' => 'Show the checkout summary and wallet balance.',
        'orders.store' => 'Place an order and process wallet payment.',
        'orders.index' => 'Show the buyer’s order history.',
        'orders.show' => 'Show an order invoice to its buyer.',
        'dashboard' => 'Show the signed-in user dashboard.',
        'login' => 'Show the sign-in form.',
        'logout' => 'Sign out and invalidate the current session.',
        'products.index' => 'Browse, search, filter, and sort products.',
        'products.show' => 'Show product details and availability.',
        'products.picture' => 'Return a product image.',
        'register' => 'Show the account registration form.',
        'seller.dashboard' => 'Show the signed-in seller dashboard.',
        'seller.products' => 'List the signed-in seller’s products.',
        'seller.products.store' => 'Create a product for the signed-in seller.',
        'seller.products.create' => 'Show the new product form.',
        'seller.products.update' => 'Update a product owned by the seller.',
        'seller.products.edit' => 'Show the edit form for a seller-owned product.',
        'seller.products.destroy' => 'Delete a product owned by the seller.',
        'seller.index' => 'List sellers for administrator management.',
        'seller.store' => 'Create a seller account as an administrator.',
        'seller.create' => 'Show the administrator’s create-seller form.',
        'seller.update' => 'Update a seller account as an administrator.',
        'seller.destroy' => 'Delete a seller when deletion is allowed.',
        'seller.edit' => 'Show the administrator’s edit-seller form.',
    ];

    $rows = [];

    foreach (Route::getRoutes() as $route) {
        $name = $route->getName();
        $uri = $route->uri();

        if ($name !== null && (str_starts_with($name, 'boost.') || str_starts_with($name, 'storage.'))) {
            continue;
        }

        $purpose = $descriptions[$name ?? ''] ?? match ($uri) {
            '/' => 'Redirect visitors to the product catalogue.',
            'up' => 'Health check endpoint for the application.',
            'register' => 'Create an account from submitted registration details.',
            'login' => 'Authenticate with a phone number and password.',
            default => 'Handle the request using '.$route->getActionName().'.',
        };

        $rows[] = [
            implode('|', $route->methods()),
            '/'.ltrim($uri, '/'),
            $name ?? '-',
            $purpose,
        ];
    }

    $this->table(['Method', 'URI', 'Route name', 'What it does'], $rows);
})->purpose('List application web routes and explain their purpose');
