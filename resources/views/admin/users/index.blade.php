<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Manage Users - Admin</title>
</head>
<body>
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>
    <p>
        <a href="{{ route('admin.dashboard') }}">← Admin Dashboard</a> |
        <a href="{{ route('admin.sellers.index') }}">Sellers</a> |
        <a href="{{ route('admin.buyers.index') }}">Buyers</a> |
        <a href="{{ route('admin.products.index') }}">Products</a> |
        <a href="{{ route('admin.orders.index') }}">Orders</a>
    </p>

    <hr>

    <h1>Manage Users</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    {{-- Filter Form --}}
    <fieldset>
        <legend>Filter Users</legend>
        <form method="GET" action="{{ route('admin.users.index') }}">
            <label>Search:</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or phone number">
            &nbsp;&nbsp;

            <label>User Type:</label>
            <select name="type">
                <option value="">-- All Types --</option>
                <option value="admin" {{ request('type') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="seller" {{ request('type') === 'seller' ? 'selected' : '' }}>Seller</option>
                <option value="buyer" {{ request('type') === 'buyer' ? 'selected' : '' }}>Buyer</option>
            </select>
            &nbsp;&nbsp;

            <button type="submit" style="margin-left: 42%;">Filter</button>
            <a href="{{ route('admin.users.index') }}"  style="margin-left: 1%;">Reset</a>
        </form>
    </fieldset>

    <br>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Wallet Balance</th>
                <th>Products</th>
                <th>Orders</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td align="center">{{ $user->user_id }}</td>
                    <td><strong>{{ $user->user_name }}</strong></td>
                    <td>{{ $user->phone_no }}</td>
                    <td align="center">
                        @if ($user->user_type === 'admin')
                            <strong style="color: purple;">Admin</strong>
                        @elseif ($user->user_type === 'seller')
                            <strong style="color: blue;">Seller</strong>
                        @else
                            <strong style="color: green;">Buyer</strong>
                        @endif
                    </td>
                    <td align="right">₹{{ number_format($user->user_wallet_amount, 2) }}</td>
                    <td align="center">{{ $user->products_count }}</td>
                    <td align="center">{{ $user->orders_count }}</td>
                    <td align="center">
                        <a href="{{ route('admin.users.edit', $user->user_id) }}">Edit Role/Wallet</a>
                        @if ($user->user_id !== auth()->id())
                            &nbsp;|&nbsp;
                            <form action="{{ route('admin.users.destroy', $user->user_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete user {{ $user->user_name }}?')">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <div>
        {{ $users->links() }}
    </div>

</body>
</html>
