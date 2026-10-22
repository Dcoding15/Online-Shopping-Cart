<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Buyers Overview - Admin</title>
</head>
<body>
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>

    <p>
        <a href="{{ route('admin.dashboard') }}">← Admin Dashboard</a> |
        <a href="{{ route('admin.users.index') }}">All Users</a> |
        <a href="{{ route('admin.sellers.index') }}">Sellers</a> |
        <a href="{{ route('admin.products.index') }}">Products</a>
    </p>

    <hr>

    <h1>Buyers Overview</h1>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Buyer ID</th>
                <th>Buyer Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Total Orders</th>
                <th>Wallet Balance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($buyers as $buyer)
                <tr>
                    <td align="center">{{ $buyer->user_id }}</td>
                    <td><strong>{{ $buyer->user_name }}</strong></td>
                    <td>{{ $buyer->phone_no }}</td>
                    <td>{{ $buyer->user_addr ?? 'N/A' }}</td>
                    <td align="center">{{ $buyer->orders_count }}</td>
                    <td align="right">₹{{ number_format($buyer->user_wallet_amount, 2) }}</td>
                    <td align="center">
                        <a href="{{ route('admin.users.edit', $buyer->user_id) }}">Edit Role/Wallet</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <div>
        {{ $buyers->links() }}
    </div>

</body>
</html>
