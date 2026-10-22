<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Checkout & Payment</title>
</head>
<body>
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>

    <p>
        <a href="{{ route('cart.index') }}">← Back to Cart</a> |
        <a href="{{ route('products.index') }}">Continue Shopping</a>
    </p>

    <hr>

    <h1>Order Checkout</h1>

    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h2>1. Order Summary</h2>
    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Product</th>
                <th>Seller</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cartItems as $item)
                <tr>
                    <td>{{ $item->product->product_name }}</td>
                    <td>{{ $item->product->seller->user_name ?? 'N/A' }}</td>
                    <td align="center">{{ $item->quantity }}</td>
                    <td align="right">₹{{ number_format($item->product->product_price, 2) }}</td>
                    <td align="right">₹{{ number_format($item->quantity * $item->product->product_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" align="right">Total Quantity:</th>
                <td align="right">{{ $totalQuantity }} item(s)</td>
            </tr>
            <tr>
                <th colspan="4" align="right">Order Grand Total:</th>
                <td align="right"><strong>₹{{ number_format($totalPrice, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <br>

    <h2>2. Payment & Wallet Verification</h2>
    <table border="1" cellpadding="8" cellspacing="0" style="max-width: 450px;">
        <tr>
            <th>Your Current Wallet Balance:</th>
            <td align="right"><strong>₹{{ number_format($buyer->user_wallet_amount, 2) }}</strong></td>
        </tr>
        <tr>
            <th>Order Total:</th>
            <td align="right">₹{{ number_format($totalPrice, 2) }}</td>
        </tr>
        <tr>
            <th>Balance After Purchase:</th>
            <td align="right">
                @if ($buyer->user_wallet_amount >= $totalPrice)
                    <span style="color: green; font-weight: bold;">
                        ₹{{ number_format($buyer->user_wallet_amount - $totalPrice, 2) }}
                    </span>
                @else
                    <span style="color: red; font-weight: bold;">
                        Short by ₹{{ number_format($totalPrice - $buyer->user_wallet_amount, 2) }}
                    </span>
                @endif
            </td>
        </tr>
    </table>

    @if ($buyer->user_wallet_amount < $totalPrice)
        <p style="color: red; font-weight: bold;">
            ⚠️ Insufficient funds! You cannot complete this checkout because your wallet balance is less than the order total.
        </p>
    @endif

    <br>

    <h2>3. Shipping Address & Confirmation</h2>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <label for="shipping_address">Delivery Address:</label><br>
        <textarea id="shipping_address" name="shipping_address" rows="3" cols="50" required>{{ old('shipping_address', $buyer->user_addr) }}</textarea>
        <br><br>

        <p><strong>Payment Method:</strong> User Wallet (Instant deduction from wallet upon confirmation)</p>

        @if ($buyer->user_wallet_amount >= $totalPrice)
            <button type="submit" onclick="return confirm('Confirm and pay ₹{{ number_format($totalPrice, 2) }} from your wallet?')">
                Confirm & Pay Now
            </button>
        @else
            <button type="button" disabled title="Insufficient wallet balance">
                Confirm & Pay Now (Disabled: Insufficient Balance)
            </button>
        @endif
    </form>

</body>
</html>
