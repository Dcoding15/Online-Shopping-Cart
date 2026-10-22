<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display buyer's cart with items and total calculation.
     */
    public function index()
    {
        $user = Auth::user();

        $cartItems = Cart::with(['product.seller'])
            ->where('user_id', $user->user_id)
            ->get();

        $totalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->product_price ?? 0);
        });

        $totalQuantity = $cartItems->sum('quantity');

        return view('cart.index', compact('cartItems', 'totalPrice', 'totalQuantity'));
    }

    /**
     * Add a product to the buyer's cart with stock validation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,product_id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $requestedQuantity = (int) ($validated['quantity'] ?? 1);
        $product = Product::findOrFail($validated['product_id']);
        $user = Auth::user();

        if ($product->product_available_quantity <= 0) {
            return back()->with('error', 'This product is currently out of stock.');
        }

        // Prevent sellers from buying their own products
        if ($product->user_id === $user->user_id) {
            return back()->with('error', 'You cannot add your own product to your cart.');
        }

        $existingCartItem = Cart::where('user_id', $user->user_id)
            ->where('product_id', $product->product_id)
            ->first();

        if ($existingCartItem) {
            $newQuantity = $existingCartItem->quantity + $requestedQuantity;

            if ($newQuantity > $product->product_available_quantity) {
                return back()->with(
                    'error',
                    "Cannot add {$requestedQuantity} more item(s). Only {$product->product_available_quantity} available in stock (you already have {$existingCartItem->quantity} in your cart)."
                );
            }

            $existingCartItem->update(['quantity' => $newQuantity]);
        } else {
            if ($requestedQuantity > $product->product_available_quantity) {
                return back()->with(
                    'error',
                    "Cannot add requested quantity. Only {$product->product_available_quantity} available in stock."
                );
            }

            Cart::create([
                'user_id' => $user->user_id,
                'product_id' => $product->product_id,
                'quantity' => $requestedQuantity,
            ]);
        }

        $user->updateCartQuantity();

        return redirect()
            ->route('cart.index')
            ->with('success', "'{$product->product_name}' added to cart.");
    }

    /**
     * Update quantity for an existing cart item.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $cartItem = Cart::with('product')
            ->where('cart_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $newQuantity = (int) $validated['quantity'];

        if ($newQuantity > $cartItem->product->product_available_quantity) {
            return redirect()
                ->route('cart.index')
                ->with(
                    'error',
                    "Requested quantity exceeds available stock ({$cartItem->product->product_available_quantity} available)."
                );
        }

        $cartItem->update(['quantity' => $newQuantity]);
        $user->updateCartQuantity();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Cart item quantity updated.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $cartItem = Cart::where('cart_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $cartItem->delete();
        $user->updateCartQuantity();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Item removed from cart.');
    }

    /**
     * Clear all items in the buyer's cart.
     */
    public function clear()
    {
        $user = Auth::user();

        Cart::where('user_id', $user->user_id)->delete();
        $user->updateCartQuantity();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Your cart has been cleared.');
    }
}
