<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>My Orders - Order History</title>
</head>
<body>
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>

    <p>
        <a href="{{ route('products.index') }}">← Browse Products</a> |
        <a href="{{ route('cart.index') }}">My Cart</a> |
        <a href="{{ route('dashboard') }}">My Dashboard</a>
    </p>

    <hr>

    <h1>My Order History</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if ($orders->isEmpty())
        <p>You have not placed any orders yet.</p>
        <p><a href="{{ route('products.index') }}">Start shopping now</a>.</p>
    @else
        <table border="1" cellpadding="8" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items Count</th>
                    <th>Total Amount</th>
                    <th>Order Status</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td align="center"><strong>#{{ $order->order_id }}</strong></td>
                        <td>{{ $order->created_at ? $order->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                        <td align="center">{{ $order->items->sum('quantity') }}</td>
                        <td align="right"><strong>₹{{ number_format($order->order_total_amount, 2) }}</strong></td>
                        <td align="center">
                            @if ($order->order_status === 'paid')
                                <span style="color: green; font-weight: bold;">Paid</span>
                            @else
                                <span>{{ ucfirst($order->order_status) }}</span>
                            @endif
                        </td>
                        <td align="center">
                            @if ($order->payment_confirmed)
                                <span style="color: green;">Confirmed</span>
                            @else
                                <span style="color: red;">Pending</span>
                            @endif
                        </td>
                        <td align="center">
                            <a href="{{ route('orders.show', $order->order_id) }}">View Invoice</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
