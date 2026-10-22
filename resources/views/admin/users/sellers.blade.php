<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Sellers Overview - Admin</title>
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
        <a href="{{ route('admin.buyers.index') }}">Buyers</a> |
        <a href="{{ route('admin.products.index') }}">Products</a>
    </p>

    <hr>

    <h1>Sellers Overview</h1>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Seller ID</th>
                <th>Seller Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Total Products</th>
                <th>Wallet Balance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sellers as $seller)
                <tr>
                    <td align="center">{{ $seller->user_id }}</td>
                    <td><strong>{{ $seller->user_name }}</strong></td>
                    <td>{{ $seller->phone_no }}</td>
                    <td>{{ $seller->user_addr ?? 'N/A' }}</td>
                    <td align="center">{{ $seller->products_count }}</td>
                    <td align="right">₹{{ number_format($seller->user_wallet_amount, 2) }}</td>
                    <td align="center">
                        <a href="{{ route('admin.users.edit', $seller->user_id) }}">Edit Role/Wallet</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <div>
        {{ $sellers->links() }}
    </div>

</body>
</html>
