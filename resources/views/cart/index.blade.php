<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Shopping Cart</title>
</head>
<body>
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>
    <p>
        <a href="{{ route('dashboard') }}">My Dashboard</a>
    </p>

    <hr>

    <h1>My Shopping Cart</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if ($cartItems->count() === 0)
        <p>Your cart is currently empty.</p>
        <p><a href="{{ route('products.index') }}">Browse products to start shopping</a>.</p>
    @else
        <table border="1" cellpadding="8" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Seller</th>
                    <th>Unit Price</th>
                    <th>Available Stock</th>
                    <th>Quantity</th>
                    <th>Item Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cartItems as $item)
                    <tr>
                        <td>
                            <strong>
                                <a href="{{ route('products.show', $item->product->product_id) }}">
                                    {{ $item->product->product_name }}
                                </a>
                            </strong>
                            <br>
                            <small>{{ $item->product->product_category }} &bull; {{ $item->product->product_type }}</small>
                        </td>
                        <td>
                            {{ $item->product->seller->user_name ?? 'N/A' }}
                        </td>
                        <td>
                            ₹{{ number_format($item->product->product_price, 2) }}
                        </td>
                        <td>
                            @if ($item->product->product_available_quantity >= $item->quantity)
                                <span style="color: green;">{{ $item->product->product_available_quantity }} in stock</span>
                            @else
                                <span style="color: red; font-weight: bold;">
                                    Only {{ $item->product->product_available_quantity }} left! (Reduce quantity)
                                </span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('cart.update', $item->cart_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->product_available_quantity }}" style="width: 60px;">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td>
                            <strong>₹{{ number_format($item->quantity * $item->product->product_price, 2) }}</strong>
                        </td>
                        <td>
                            <form action="{{ route('cart.destroy', $item->cart_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Remove this item from your cart?')">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br>

        {{-- Cart Summary --}}
        <table border="1" cellpadding="8" cellspacing="0" style="max-width: 400px; margin-left: auto;">
            <tr>
                <th align="left">Total Items:</th>
                <td align="right">{{ $totalQuantity }}</td>
            </tr>
            <tr>
                <th align="left">Cart Grand Total:</th>
                <td align="right"><strong>₹{{ number_format($totalPrice, 2) }}</strong></td>
            </tr>
            <tr>
                <th align="left">Your Wallet Balance:</th>
                <td align="right">₹{{ number_format(auth()->user()->user_wallet_amount, 2) }}</td>
            </tr>
        </table>

        <br>

        <div style="text-align: right;">
            <form action="{{ route('cart.clear') }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Are you sure you want to empty your entire cart?')">
                    Clear Entire Cart
                </button>
            </form>
            &nbsp;&nbsp;
            <a href="{{ route('products.index') }}">Continue Shopping</a>
            &nbsp;&nbsp;
            <a href="{{ route('checkout') }}" style="font-weight: bold; font-size: 1.1em;">Proceed to Checkout →</a>
        </div>
    @endif

</body>
</html>
