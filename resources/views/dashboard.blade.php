<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Dashboard</title>
</head>
<body>
    @include('partials.app-header')
    <!-- <h1>User Dashboard</h1> -->

    <section class="dashboard-profile" aria-label="Profile">
        <div class="dashboard-avatar">
            @if ($user->user_profile_pic)
                <img
                    src="{{ route('profile.picture') }}"
                    alt="{{ $user->user_name }}'s profile picture"
                >
            @else
                <span class="dashboard-avatar-placeholder" aria-hidden="true">
                    {{ strtoupper(substr($user->user_name ?? 'U', 0, 1)) }}
                </span>
                <span class="dashboard-avatar-caption">No profile picture</span>
            @endif
        </div>

        <div class="dashboard-profile-details">
            <h2>Welcome, {{ $user->user_name }}</h2>

            <table>
                <tr>
                    <th>User Type:</th>
                    <td>{{ ucfirst($user->user_type) }}</td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td>{{ $user->phone_no }}</td>
                </tr>
                <tr>
                    <th>Address:</th>
                    <td>{{ $user->user_addr ?? 'Not set' }}</td>
                </tr>
            </table>
        </div>
    </section>

    <br>

    <fieldset class="page-navigation">
        <legend>Navigation</legend>
        <nav aria-label="Account navigation">
            <ul>
                @if ($user->isBuyer())
                    <li><a href="{{ route('products.index') }}">Browse Products</a></li>
                    <li><a href="{{ route('cart.index') }}">My Shopping Cart ({{ $user->user_cart_quantity ?? 0 }})</a></li>
                    <li><a href="{{ route('orders.index') }}">My Order History</a></li>
                @endif

                @if ($user->isSeller())
                    <li><a href="{{ route('seller.dashboard') }}">Seller Dashboard</a></li>
                    <li><a href="{{ route('seller.products') }}">Manage My Products</a></li>
                    <li><a href="{{ route('seller.products.create') }}">Add New Product</a></li>
                @endif

                @if ($user->isAdmin())
                    <li><a href="{{ route('cart.index') }}">My Shopping Cart ({{ $user->user_cart_quantity ?? 0 }})</a></li>
                    <li><a href="{{ route('orders.index') }}">My Order History</a></li>
                    <li><strong><a href="{{ route('admin.dashboard') }}">Admin Dashboard</a></strong></li>
                    <li><a href="{{ route('admin.users.index') }}">Manage Users</a></li>
                    <li><a href="{{ route('admin.products.index') }}">Manage Products</a></li>
                    <li><a href="{{ route('admin.orders.index') }}">Manage Orders</a></li>
                @endif
            </ul>
        </nav>
    </fieldset>

</body>
</html>