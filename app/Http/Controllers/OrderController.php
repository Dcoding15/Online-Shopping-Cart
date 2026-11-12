<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Show checkout page with order preview and shipping address.
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
     * Create a pending order and its Razorpay order for the checkout widget.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string|max:255',
        ]);

        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (! is_string($keyId) || $keyId === '' || ! is_string($keySecret) || $keySecret === '') {
            return response()->json([
                'message' => 'Online payments are not configured. Please contact the store administrator.',
            ], 503);
        }

        $buyer = Auth::user();
        $order = DB::transaction(function () use ($buyer, $validated): Order {
            $cartItems = Cart::where('user_id', $buyer->user_id)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty. Please add items before checking out.',
                ]);
            }

            $snapshots = [];
            $totalPaise = 0;

            foreach ($cartItems as $cartItem) {
                $product = Product::where('product_id', $cartItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product || $product->product_available_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'A product in your cart no longer has enough stock. Please review your cart.',
                    ]);
                }

                $unitPricePaise = (int) round((float) $product->product_price * 100);
                $subtotalPaise = $unitPricePaise * $cartItem->quantity;
                $totalPaise += $subtotalPaise;

                $snapshots[] = [
                    'product_id' => $product->product_id,
                    'seller_id' => $product->user_id,
                    'product_name' => $product->product_name,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $product->product_price,
                    'subtotal' => number_format($subtotalPaise / 100, 2, '.', ''),
                ];
            }

            if ($totalPaise < 100) {
                throw ValidationException::withMessages([
                    'cart' => 'The order total must be at least ₹1.00 for online payment.',
                ]);
            }

            $order = Order::create([
                'user_id' => $buyer->user_id,
                'order_total_amount' => number_format($totalPaise / 100, 2, '.', ''),
                'order_status' => 'pending',
                'payment_confirmed' => false,
                'payment_provider' => 'razorpay',
                'shipping_address' => $validated['shipping_address'],
                'created_at' => now(),
            ]);

            foreach ($snapshots as $snapshot) {
                OrderItem::create([
                    ...$snapshot,
                    'order_id' => $order->order_id,
                ]);
            }

            return $order;
        });

        $amountPaise = (int) round((float) $order->order_total_amount * 100);

        try {
            $gatewayOrder = $this->razorpayClient($keyId, $keySecret)
                ->post(rtrim(config('services.razorpay.api_url'), '/').'/orders', [
                    'amount' => $amountPaise,
                    'currency' => 'INR',
                    'receipt' => 'order-'.$order->order_id,
                    'notes' => [
                        'local_order_id' => (string) $order->order_id,
                        'buyer_id' => (string) $buyer->user_id,
                    ],
                ])
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $exception) {
            $order->delete();
            Log::error('Razorpay order creation failed.', [
                'order_id' => $order->order_id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to start online payment. Please try again.',
            ], 502);
        }

        if (
            ! is_array($gatewayOrder)
            || ! isset($gatewayOrder['id'], $gatewayOrder['amount'], $gatewayOrder['currency'])
            || ! is_string($gatewayOrder['id'])
            || (int) $gatewayOrder['amount'] !== $amountPaise
            || $gatewayOrder['currency'] !== 'INR'
        ) {
            $order->delete();
            Log::error('Razorpay returned an invalid order response.', [
                'order_id' => $order->order_id,
            ]);

            return response()->json([
                'message' => 'Unable to start online payment. Please try again.',
            ], 502);
        }

        $order->update(['razorpay_order_id' => $gatewayOrder['id']]);

        return response()->json([
            'key' => $keyId,
            'razorpay_order_id' => $gatewayOrder['id'],
            'amount' => $amountPaise,
            'currency' => 'INR',
            'name' => config('app.name'),
            'description' => 'Order #'.$order->order_id,
            'prefill' => [
                'name' => $buyer->user_name,
                'contact' => $buyer->phone_no,
            ],
            'verify_url' => route('orders.verify'),
        ]);
    }

    /**
     * Verify the Razorpay signature and payment status before fulfilling an order.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string|max:100',
            'razorpay_payment_id' => 'required|string|max:100',
            'razorpay_signature' => 'required|string|max:255',
        ]);

        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (! is_string($keyId) || $keyId === '' || ! is_string($keySecret) || $keySecret === '') {
            return response()->json([
                'message' => 'Online payments are not configured. Please contact the store administrator.',
            ], 503);
        }

        $order = Order::where('user_id', Auth::id())
            ->where('razorpay_order_id', $validated['razorpay_order_id'])
            ->firstOrFail();

        $expectedSignature = hash_hmac(
            'sha256',
            $validated['razorpay_order_id'].'|'.$validated['razorpay_payment_id'],
            $keySecret
        );

        if (! hash_equals($expectedSignature, $validated['razorpay_signature'])) {
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        if (
            $order->razorpay_payment_id === $validated['razorpay_payment_id']
            && in_array($order->order_status, ['paid', 'refund_required', 'refund_pending', 'refunded'], true)
        ) {
            return response()->json([
                'redirect_url' => route('orders.show', $order->order_id),
            ]);
        }

        if ($order->order_status !== 'pending') {
            return response()->json(['message' => 'This order is no longer awaiting payment.'], 409);
        }

        $paymentUrl = rtrim(config('services.razorpay.api_url'), '/').'/payments/'.rawurlencode($validated['razorpay_payment_id']);
        $expectedAmountPaise = (int) round((float) $order->order_total_amount * 100);

        try {
            $payment = $this->razorpayClient($keyId, $keySecret)
                ->get($paymentUrl)
                ->throw()
                ->json();

            if (
                ! is_array($payment)
                || ($payment['id'] ?? null) !== $validated['razorpay_payment_id']
                || ($payment['order_id'] ?? null) !== $validated['razorpay_order_id']
                || (int) ($payment['amount'] ?? 0) !== $expectedAmountPaise
                || ($payment['currency'] ?? null) !== 'INR'
            ) {
                return response()->json(['message' => 'The payment details do not match this order.'], 422);
            }

            if (($payment['status'] ?? null) === 'authorized') {
                $payment = $this->razorpayClient($keyId, $keySecret)
                    ->post($paymentUrl.'/capture', [
                        'amount' => $expectedAmountPaise,
                        'currency' => 'INR',
                    ])
                    ->throw()
                    ->json();
            }
        } catch (ConnectionException|RequestException $exception) {
            Log::error('Razorpay payment verification request failed.', [
                'order_id' => $order->order_id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to confirm payment right now. Please try again shortly.',
            ], 502);
        }

        if (
            ! is_array($payment)
            || ($payment['id'] ?? null) !== $validated['razorpay_payment_id']
            || ($payment['order_id'] ?? null) !== $validated['razorpay_order_id']
            || (int) ($payment['amount'] ?? 0) !== $expectedAmountPaise
            || ($payment['currency'] ?? null) !== 'INR'
            || ($payment['status'] ?? null) !== 'captured'
        ) {
            return response()->json([
                'message' => 'Payment has not been captured. Please try again or choose another payment method.',
            ], 422);
        }

        $paymentMethod = is_string($payment['method'] ?? null)
            ? substr($payment['method'], 0, 32)
            : null;

        $fulfillmentStatus = DB::transaction(function () use ($order, $validated, $paymentMethod): string {
            $lockedOrder = Order::where('order_id', $order->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->order_status !== 'pending') {
                return $lockedOrder->order_status;
            }

            $orderItems = $lockedOrder->items()->orderBy('product_id')->get();
            $quantities = $orderItems->groupBy('product_id')
                ->map(fn ($items) => $items->sum('quantity'));
            $products = [];

            foreach ($quantities as $productId => $quantity) {
                $product = $productId
                    ? Product::where('product_id', $productId)->lockForUpdate()->first()
                    : null;

                if (! $product || $product->product_available_quantity < $quantity) {
                    $lockedOrder->update([
                        'order_status' => 'refund_required',
                        'razorpay_payment_id' => $validated['razorpay_payment_id'],
                        'payment_method' => $paymentMethod,
                    ]);

                    return 'refund_required';
                }

                $products[$productId] = [$product, $quantity];
            }

            if ($orderItems->isEmpty()) {
                $lockedOrder->update([
                    'order_status' => 'refund_required',
                    'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    'payment_method' => $paymentMethod,
                ]);

                return 'refund_required';
            }

            foreach ($products as [$product, $quantity]) {
                $product->decrement('product_available_quantity', $quantity);
            }

            $buyer = User::where('user_id', $lockedOrder->user_id)
                ->lockForUpdate()
                ->firstOrFail();
            $buyer->user_addr = $lockedOrder->shipping_address;
            $buyer->save();

            $lockedOrder->update([
                'order_status' => 'paid',
                'payment_confirmed' => true,
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'payment_method' => $paymentMethod,
            ]);

            foreach ($orderItems as $orderItem) {
                if ($orderItem->product_id === null) {
                    continue;
                }

                $cartItem = Cart::where('user_id', $lockedOrder->user_id)
                    ->where('product_id', $orderItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($cartItem) {
                    if ($cartItem->quantity <= $orderItem->quantity) {
                        $cartItem->delete();
                    } else {
                        $cartItem->decrement('quantity', $orderItem->quantity);
                    }
                }
            }

            $buyer->updateCartQuantity();

            return 'paid';
        });

        if ($fulfillmentStatus === 'refund_required') {
            return $this->refundUnavailableOrder(
                $order,
                $validated['razorpay_payment_id'],
                (int) round((float) $order->order_total_amount * 100),
                $keyId,
                $keySecret
            );
        }

        if ($fulfillmentStatus !== 'paid') {
            return response()->json(['message' => 'This order is no longer awaiting payment.'], 409);
        }

        return response()->json([
            'redirect_url' => route('orders.show', $order->order_id),
        ]);
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

    private function razorpayClient(string $keyId, string $keySecret): PendingRequest
    {
        return Http::withBasicAuth($keyId, $keySecret)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    private function refundUnavailableOrder(
        Order $order,
        string $paymentId,
        int $amountPaise,
        string $keyId,
        string $keySecret
    ): JsonResponse {
        try {
            $refund = $this->razorpayClient($keyId, $keySecret)
                ->post(
                    rtrim(config('services.razorpay.api_url'), '/').'/payments/'.rawurlencode($paymentId).'/refund',
                    ['amount' => $amountPaise]
                )
                ->throw()
                ->json();
        } catch (ConnectionException|RequestException $exception) {
            Log::error('Razorpay refund request failed.', [
                'order_id' => $order->order_id,
                'payment_id' => $paymentId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment was received, but the order could not be fulfilled. Please contact support to complete the refund.',
                'redirect_url' => route('orders.show', $order->order_id),
            ], 502);
        }

        if (! is_array($refund)) {
            Log::error('Razorpay returned an invalid refund response.', [
                'order_id' => $order->order_id,
                'payment_id' => $paymentId,
            ]);

            return response()->json([
                'message' => 'Payment was received, but the refund status could not be confirmed. Please contact support.',
                'redirect_url' => route('orders.show', $order->order_id),
            ], 502);
        }

        $refundStatus = $refund['status'] ?? 'pending';
        $order->update([
            'razorpay_refund_id' => $refund['id'] ?? null,
            'order_status' => $refundStatus === 'processed'
                ? 'refunded'
                : ($refundStatus === 'failed' ? 'refund_required' : 'refund_pending'),
        ]);

        return response()->json([
            'redirect_url' => route('orders.show', $order->order_id),
        ]);
    }
}
