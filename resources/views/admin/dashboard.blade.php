<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Admin Dashboard - System Overview</title>
</head>
<body>

    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>
    <p>
        <strong>Admin Control Panel</strong> |
        Logged in as: <strong>{{ auth()->user()->user_name }}</strong> |
        <a href="{{ route('products.index') }}">Public Store</a>
    </p>

    <hr>

    <h1>Administration Dashboard</h1>

    {{-- Navigation Menu --}}
    <fieldset>
        <legend>Admin Navigation</legend>
        <p>
            <a href="{{ route('admin.users.index') }}">Manage All Users</a> |
            <a href="{{ route('admin.sellers.index') }}">Sellers Overview</a> |
            <a href="{{ route('admin.buyers.index') }}">Buyers Overview</a> |
            <a href="{{ route('admin.products.index') }}">Platform Products</a> |
            <a href="{{ route('admin.orders.index') }}">Platform Orders</a>
        </p>
    </fieldset>

    <br>

    {{-- System Metrics Overview --}}
    <h2>System Key Metrics</h2>
    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Total Users</th>
                <th>Buyers</th>
                <th>Sellers</th>
                <th>Admins</th>
                <th>Total Products</th>
                <th>In-Stock / Out-of-Stock</th>
                <th>Total Orders</th>
                <th>Total Sales Revenue</th>
            </tr>
        </thead>
        <tbody>
            <tr align="center">
                <td><h2>{{ $totalUsers }}</h2></td>
                <td><h2>{{ $totalBuyers }}</h2></td>
                <td><h2>{{ $totalSellers }}</h2></td>
                <td><h2>{{ $totalAdmins }}</h2></td>
                <td><h2>{{ $totalProducts }}</h2></td>
                <td>
                    <span style="color: green;">{{ $inStockProducts }} In Stock</span> /
                    <span style="color: red;">{{ $outOfStockProducts }} Out</span>
                </td>
                <td><h2>{{ $totalOrders }}</h2><small>({{ $paidOrders }} paid)</small></td>
                <td><h2>₹{{ number_format($totalRevenue, 2) }}</h2></td>
            </tr>
        </tbody>
    </table>

</body>
</html>
