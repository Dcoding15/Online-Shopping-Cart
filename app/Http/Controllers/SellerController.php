<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SellerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Seller CRUD (Seller Management)
    |--------------------------------------------------------------------------
    */

    /**
     * Display a listing of all sellers.
     */
    public function index()
    {
        $sellers = User::where('user_type', 'seller')
            ->withCount('products')
            ->orderBy('user_id', 'asc')
            ->paginate(15);

        return view('seller.index', compact('sellers'));
    }

    /**
     * Show the form for creating a new seller.
     */
    public function create()
    {
        return view('seller.create');
    }

    /**
     * Store a newly created seller in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'required|string|max:100',
            'phone_no' => 'required|string|max:150|unique:users,phone_no',
            'user_addr' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'user_wallet_amount' => 'nullable|numeric|min:0',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'user_name' => $validated['user_name'],
            'phone_no' => $validated['phone_no'],
            'user_addr' => $validated['user_addr'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'user_type' => 'seller',
            'user_cart_quantity' => 0,
            'userpayment_confirmation' => false,
            'user_wallet_amount' => $validated['user_wallet_amount'] ?? 0,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('seller.index')
            ->with('success', 'Seller created successfully.');
    }

    /**
     * Show the form for editing the specified seller.
     */
    public function edit($id)
    {
        $seller = User::where('user_id', $id)
            ->where('user_type', 'seller')
            ->firstOrFail();

        return view('seller.edit', compact('seller'));
    }

    /**
     * Update the specified seller in storage.
     */
    public function update(Request $request, $id)
    {
        $seller = User::where('user_id', $id)
            ->where('user_type', 'seller')
            ->firstOrFail();

        $validated = $request->validate([
            'user_name' => 'required|string|max:100',
            'phone_no' => 'required|string|max:150|unique:users,phone_no,'.$seller->user_id.',user_id',
            'user_addr' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'user_wallet_amount' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'user_name' => $validated['user_name'],
            'phone_no' => $validated['phone_no'],
            'user_addr' => $validated['user_addr'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'user_wallet_amount' => $validated['user_wallet_amount'] ?? $seller->user_wallet_amount,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $seller->update($updateData);

        return redirect()
            ->route('seller.index')
            ->with('success', 'Seller updated successfully.');
    }

    /**
     * Remove the specified seller from storage.
     * Prevents deletion if the seller owns products.
     */
    public function destroy($id)
    {
        $seller = User::where('user_id', $id)
            ->where('user_type', 'seller')
            ->firstOrFail();

        $productCount = $seller->products()->count();

        if ($productCount > 0) {
            return redirect()
                ->route('seller.index')
                ->with('error', "Cannot delete seller '{$seller->user_name}' because they have {$productCount} product(s). Please delete or reassign their products first.");
        }

        $seller->delete();

        return redirect()
            ->route('seller.index')
            ->with('success', 'Seller deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Seller Product Management (for logged-in seller)
    |--------------------------------------------------------------------------
    */

    /**
     * Display seller dashboard.
     */
    public function dashboard()
    {
        $sellerId = Auth::id();
        $products = Product::where('user_id', $sellerId)->get();

        return view('seller.dashboard', compact('products'));
    }

    /**
     * Display seller's products ("My Products").
     */
    public function products()
    {
        $sellerId = Auth::id();
        $products = Product::where('user_id', $sellerId)->paginate(15);

        return view('seller.products.index', compact('products'));
    }

    /**
     * Show form for seller to create a product.
     */
    public function createProduct()
    {
        $categories = Product::CATEGORIES;
        $types = Product::TYPES;

        return view('seller.products.create', compact('categories', 'types'));
    }

    /**
     * Store a product created by seller.
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:200',
            'product_available_quantity' => 'required|integer|min:0',
            'product_price' => 'required|numeric|min:0',
            'product_picture' => 'nullable|image|max:4096',
            'product_type' => 'required|string|max:100',
            'product_description' => 'nullable|string',
            'product_category' => 'required|string|max:100',
            'product_meta_data' => 'nullable|string',
        ]);

        $pictureBinary = null;
        if ($request->hasFile('product_picture')) {
            $pictureBinary = file_get_contents($request->file('product_picture')->getRealPath());
        }

        $metaData = null;
        if (! empty($validated['product_meta_data'])) {
            $decoded = json_decode($validated['product_meta_data'], true);
            $metaData = (json_last_error() === JSON_ERROR_NONE) ? $decoded : ['meta' => $validated['product_meta_data']];
        }

        Product::create([
            'product_name' => $validated['product_name'],
            'user_id' => Auth::id(),
            'product_available_quantity' => $validated['product_available_quantity'],
            'product_price' => $validated['product_price'],
            'product_picture' => $pictureBinary,
            'product_type' => $validated['product_type'],
            'product_description' => $validated['product_description'] ?? null,
            'product_category' => $validated['product_category'],
            'product_meta_data' => $metaData,
        ]);

        return redirect()
            ->route('seller.products')
            ->with('success', 'Product added successfully.');
    }

    /**
     * Show form for seller to edit their product.
     */
    public function editProduct($id)
    {
        $product = Product::where('product_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $categories = Product::CATEGORIES;
        $types = Product::TYPES;

        return view('seller.products.edit', compact('product', 'categories', 'types'));
    }

    /**
     * Update seller's product.
     */
    public function updateProduct(Request $request, $id)
    {
        $product = Product::where('product_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'product_name' => 'required|string|max:200',
            'product_available_quantity' => 'required|integer|min:0',
            'product_price' => 'required|numeric|min:0',
            'product_picture' => 'nullable|image|max:4096',
            'product_type' => 'required|string|max:100',
            'product_description' => 'nullable|string',
            'product_category' => 'required|string|max:100',
            'product_meta_data' => 'nullable|string',
        ]);

        $updateData = [
            'product_name' => $validated['product_name'],
            'product_available_quantity' => $validated['product_available_quantity'],
            'product_price' => $validated['product_price'],
            'product_type' => $validated['product_type'],
            'product_description' => $validated['product_description'] ?? null,
            'product_category' => $validated['product_category'],
        ];

        if ($request->hasFile('product_picture')) {
            $updateData['product_picture'] = file_get_contents($request->file('product_picture')->getRealPath());
        }

        if (isset($validated['product_meta_data'])) {
            $decoded = json_decode($validated['product_meta_data'], true);
            $updateData['product_meta_data'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : ['meta' => $validated['product_meta_data']];
        }

        $product->update($updateData);

        return redirect()
            ->route('seller.products')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete seller's product.
     */
    public function destroyProduct($id)
    {
        $product = Product::where('product_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $product->delete();

        return redirect()
            ->route('seller.products')
            ->with('success', 'Product deleted successfully.');
    }
}
