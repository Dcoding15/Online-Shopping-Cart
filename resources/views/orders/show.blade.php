<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Order Invoice #{{ $order->order_id }}</title>
</head>
<body>
    @include('partials.app-header')

    <fieldset class="page-navigation">
        <legend>Buyer navigation</legend>
        <nav class="page-nav" aria-label="Buyer navigation">
            <a href="{{ route('orders.index') }}">← Back to Orders</a>
            <a href="{{ route('products.index') }}">Browse Catalog</a>
            <a href="{{ route('dashboard') }}">My Dashboard</a>
        </nav>
    </fieldset>

    <hr>

    <h1>Order Invoice #{{ $order->order_id }}</h1>

    @if (session('success'))
        <p style="color: green; font-weight: bold;">{{ session('success') }}</p>
    @endif

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
            <th align="left">Customer:</th>
            <td>{{ $order->buyer->user_name }} (Phone: {{ $order->buyer->phone_no }})</td>
        </tr>
        <tr>
            <th align="left">Delivery Address:</th>
            <td>{{ $order->shipping_address ?: 'Not specified' }}</td>
        </tr>
        <tr>
            <th align="left">Order Status:</th>
            <td>
                <span style="color: green; font-weight: bold;">{{ ucwords(str_replace('_', ' ', $order->order_status)) }}</span>
            </td>
        </tr>
        <tr>
            <th align="left">Payment Status:</th>
            <td>
                @if ($order->payment_confirmed)
                    <span style="color: green; font-weight: bold;">
                        @if ($order->payment_provider === 'razorpay')
                            Paid via Razorpay ({{ ucfirst($order->payment_method ?? 'Online') }})
                        @else
                            Payment confirmed
                        @endif
                    </span>
                @elseif ($order->order_status === 'refund_pending')
                    <span style="color: orange;">Refund pending</span>
                @elseif ($order->order_status === 'refund_required')
                    <span style="color: red;">Refund requires support</span>
                @elseif ($order->order_status === 'refunded')
                    <span style="color: green;">Refunded</span>
                @else
                    <span style="color: red;">Payment pending</span>
                @endif
            </td>
        </tr>
    </table>

    <h2>Purchased Items</h2>
    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Item #</th>
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
                        @if ($item->product)
                            <a href="{{ route('products.show', $item->product_id) }}">
                                {{ $item->product_name }}
                            </a>
                        @else
                            {{ $item->product_name }}
                        @endif
                    </td>
                    <td>{{ $item->seller->user_name ?? 'N/A' }}</td>
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
