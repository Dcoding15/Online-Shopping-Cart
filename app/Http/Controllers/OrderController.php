<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Show checkout page with order preview, address, and wallet balance.
     */
    public function checkout()
    {
        $buyer = Auth::user();

        $cartItems = Cart::with(['product.seller'])
            ->where('user_id', $buyer->user_id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty. Please add items before checking out.');
        }

        // Verify that all items have sufficient available stock
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->product->product_available_quantity) {
                return redirect()
                    ->route('cart.index')
                    ->with(
                        'error',
                        "Cannot proceed: '{$item->product->product_name}' only has {$item->product->product_available_quantity} available in stock, but you have {$item->quantity} in your cart."
                    );
            }
        }

        $totalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->product_price ?? 0);
        });

        $totalQuantity = $cartItems->sum('quantity');

        return view('orders.checkout', compact('buyer', 'cartItems', 'totalPrice', 'totalQuantity'));
    }

    /**
     * Process checkout, deduct wallet amount, decrement stock, and create order records.
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:255',
        ]);

        $buyer = Auth::user();

        $cartItems = Cart::with(['product.seller'])
            ->where('user_id', $buyer->user_id)
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $totalPrice = (float) $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->product_price ?? 0);
        });

        // Stock verification
        foreach ($cartItems as $item) {
            if ($item->product->product_available_quantity < $item->quantity) {
                return redirect()
                    ->route('cart.index')
                    ->with(
                        'error',
                        "Insufficient stock for '{$item->product->product_name}'. Only {$item->product->product_available_quantity} left."
                    );
            }
        }

        // Wallet balance verification
        if ((float) $buyer->user_wallet_amount < $totalPrice) {
            return back()->with(
                'error',
                'Insufficient wallet balance. Total required is ₹'.number_format($totalPrice, 2).', but your current balance is ₹'.number_format($buyer->user_wallet_amount, 2).'.'
            );
        }

        // Atomic transaction with row-level locking: deduct wallet, credit sellers, decrement stock, create order and items, clear cart
        $order = DB::transaction(function () use ($buyer, $cartItems, $totalPrice, $request) {
            // Lock buyer row to prevent concurrent checkout race conditions
            $buyer = User::where('user_id', $buyer->user_id)->lockForUpdate()->firstOrFail();

            // Re-verify wallet balance under lock
            if ((float) $buyer->user_wallet_amount < $totalPrice) {
                throw new \RuntimeException('Insufficient wallet balance (concurrent transaction detected).');
            }

            // Deduct buyer wallet
            $buyer->user_wallet_amount = (float) $buyer->user_wallet_amount - $totalPrice;
            $buyer->userpayment_confirmation = true;
            $buyer->user_addr = $request->shipping_address;
            $buyer->save();

            // Create Order
            $order = Order::create([
                'user_id' => $buyer->user_id,
                'order_total_amount' => $totalPrice,
                'order_status' => 'paid',
                'payment_confirmed' => true,
                'shipping_address' => $request->shipping_address,
                'created_at' => now(),
            ]);

            // Process each item: lock product, verify stock, credit seller, decrement stock, create order item
            foreach ($cartItems as $item) {
                // Lock the product row to prevent overselling
                $product = Product::where('product_id', $item->product_id)->lockForUpdate()->firstOrFail();

                if ($product->product_available_quantity < $item->quantity) {
                    throw new \RuntimeException("Insufficient stock for '{$product->product_name}' (concurrent transaction detected).");
                }

                $itemSubtotal = (float) ($item->quantity * $product->product_price);

                // Credit seller's wallet (lock seller row)
                if ($product->user_id) {
                    $seller = User::where('user_id', $product->user_id)->lockForUpdate()->first();
                    if ($seller) {
                        $seller->user_wallet_amount = (float) $seller->user_wallet_amount + $itemSubtotal;
                        $seller->save();
                    }
                }

                // Decrement product available quantity
                $product->decrement('product_available_quantity', $item->quantity);

                // Create Order Item
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $item->product_id,
                    'seller_id' => $product->user_id,
                    'product_name' => $product->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $product->product_price,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Clear buyer cart
            Cart::where('user_id', $buyer->user_id)->delete();
            $buyer->updateCartQuantity();

            return $order;
        });

        return redirect()
            ->route('orders.show', $order->order_id)
            ->with('success', 'Order #'.$order->order_id.' placed and paid successfully! ₹'.number_format($totalPrice, 2).' deducted from your wallet.');
    }

    /**
     * Display buyer's order history.
     */
    public function index()
    {
        $buyer = Auth::user();

        $orders = Order::with('items')
            ->where('user_id', $buyer->user_id)
            ->orderBy('order_id', 'desc')
            ->get();

        return view('orders.index', compact('orders'));
    }

    /**
     * Display a single order invoice/receipt.
     */
    public function show($id)
    {
        $buyer = Auth::user();

        $order = Order::with(['items.seller', 'buyer'])
            ->where('order_id', $id)
            ->where('user_id', $buyer->user_id)
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }
}
