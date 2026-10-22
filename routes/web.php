<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/products');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Public Products
|--------------------------------------------------------------------------
*/

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product}/picture', [ProductController::class, 'picture'])->name('products.picture');

/*
|--------------------------------------------------------------------------
| Seller Management (Seller CRUD)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/sellers', [SellerController::class, 'index'])->name('seller.index');
    Route::get('/sellers/create', [SellerController::class, 'create'])->name('seller.create');
    Route::post('/sellers', [SellerController::class, 'store'])->name('seller.store');
    Route::get('/sellers/{id}/edit', [SellerController::class, 'edit'])->name('seller.edit');
    Route::put('/sellers/{id}', [SellerController::class, 'update'])->name('seller.update');
    Route::delete('/sellers/{id}', [SellerController::class, 'destroy'])->name('seller.destroy');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile/picture', [CustomerController::class, 'picture'])->name('profile.picture');

    /*
    |--------------------------------------------------------------------------
    | Seller Product Management ("My Products")
    |--------------------------------------------------------------------------
    */
    Route::middleware('seller')->group(function () {
        Route::get('/seller/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');
        Route::get('/seller/products', [SellerController::class, 'products'])->name('seller.products');
        Route::get('/seller/products/create', [SellerController::class, 'createProduct'])->name('seller.products.create');
        Route::post('/seller/products', [SellerController::class, 'storeProduct'])->name('seller.products.store');
        Route::get('/seller/products/{id}/edit', [SellerController::class, 'editProduct'])->name('seller.products.edit');
        Route::put('/seller/products/{id}', [SellerController::class, 'updateProduct'])->name('seller.products.update');
        Route::delete('/seller/products/{id}', [SellerController::class, 'destroyProduct'])->name('seller.products.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Cart Management (Buyers only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('buyer')->group(function () {
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
        Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

        /*
        |--------------------------------------------------------------------------
        | Orders & Checkout
        |--------------------------------------------------------------------------
        */
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Module
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/sellers', [AdminController::class, 'sellers'])->name('sellers.index');
    Route::get('/buyers', [AdminController::class, 'buyers'])->name('buyers.index');
    Route::get('/products', [AdminController::class, 'products'])->name('products.index');
    Route::delete('/products/{id}', [AdminController::class, 'destroyProduct'])->name('products.destroy');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{id}', [AdminController::class, 'showOrder'])->name('orders.show');
});
