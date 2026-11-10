<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Buyer / Public Product Catalog
    |--------------------------------------------------------------------------
    */

    /**
     * Display a listing of products with search and filtering for buyers.
     */
    public function index(Request $request)
    {
        $query = Product::with('seller');

        // Search by keyword in name or description (PostgreSQL case-insensitive ilike)
        if ($request->filled('search')) {
            $search = '%'.trim($request->search).'%';
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'ilike', $search)
                    ->orWhere('product_description', 'ilike', $search);
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('product_category', $request->category);
        }

        // Filter by product type
        if ($request->filled('type')) {
            $query->where('product_type', $request->type);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('product_price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('product_price', '<=', (float) $request->max_price);
        }

        // In-stock only filter
        if ($request->boolean('in_stock')) {
            $query->where('product_available_quantity', '>', 0);
        }

        // Sorting options
        $sortBy = $request->get('sort', 'newest');
        match ($sortBy) {
            'price_asc' => $query->orderBy('product_price', 'asc'),
            'price_desc' => $query->orderBy('product_price', 'desc'),
            'name_asc' => $query->orderBy('product_name', 'asc'),
            default => $query->orderBy('product_id', 'desc'),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Product::CATEGORIES;
        $types = Product::TYPES;

        return view('products.index', compact('products', 'categories', 'types'));
    }

    /**
     * Display product details for buyer.
     */
    public function show(Product $product)
    {
        $product->load('seller');

        return view('products.show', compact('product'));
    }

    /**
     * Serve the product picture from public storage.
     */
    public function picture(Product $product)
    {
        if (empty($product->product_picture)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($product->product_picture);

        if (! file_exists($path)) {
            abort(404);
        }

        $mimeType = mime_content_type($path) ?: 'image/jpeg';

        return response()->file($path, ['Content-Type' => $mimeType]);
    }
}
