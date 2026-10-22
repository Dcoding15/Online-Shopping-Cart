<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Admin - Order Invoice #{{ $order->order_id }}</title>
</head>
<body>
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>

    <p>
        <a href="{{ route('admin.orders.index') }}">← Back to All Orders</a> |
        <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
    </p>

    <hr>

    <h1>Order Invoice #{{ $order->order_id }} (Admin View)</h1>

    <table border="1" cellpadding="8" cellspacing="0" style="max-width: 600px; margin-bottom: 20px;">
        <tr>
            <th align="left">Order Number:</th>
            <td>#{{ $order->order_id }}</td>
        </tr>
        <tr>
            <th align="left">Order Date:</th>
            <td>{{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
        </tr>
        <tr>
            <th align="left">Buyer:</th>
            <td>{{ $order->buyer->user_name ?? 'N/A' }} (Phone: {{ $order->buyer->phone_no ?? 'N/A' }})</td>
        </tr>
        <tr>
            <th align="left">Shipping Address:</th>
            <td>{{ $order->shipping_address ?: 'Not specified' }}</td>
        </tr>
        <tr>
            <th align="left">Order Status:</th>
            <td><strong style="color: green;">{{ ucfirst($order->order_status) }}</strong></td>
        </tr>
        <tr>
            <th align="left">Payment Status:</th>
            <td>
                @if ($order->payment_confirmed)
                    <strong style="color: green;">Confirmed (Wallet)</strong>
                @else
                    <strong style="color: red;">Pending</strong>
                @endif
            </td>
        </tr>
    </table>

    <h2>Order Items & Seller Breakdown</h2>
    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Seller</th>
                <th>Unit Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $index => $item)
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if ($item->product)
                            <br><small><a href="{{ route('products.show', $item->product_id) }}" target="_blank">View Live Product</a></small>
                        @endif
                    </td>
                    <td>{{ $item->seller->user_name ?? 'N/A' }} (ID: {{ $item->seller_id }})</td>
                    <td align="right">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td align="center">{{ $item->quantity }}</td>
                    <td align="right">₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" align="right">Grand Total Paid:</th>
                <td align="right"><strong>₹{{ number_format($order->order_total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
