<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Platform Products - Admin Oversight</title>
</head>
<body>
    <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" style="margin-left: 89%;">Logout</button>
    </form>

    <p>
        <a href="{{ route('admin.dashboard') }}">← Admin Dashboard</a> |
        <a href="{{ route('admin.users.index') }}">Manage Users</a> |
        <a href="{{ route('admin.orders.index') }}">Manage Orders</a>
    </p>

    <hr>

    <h1>Platform Products Oversight</h1>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    {{-- Filter Form --}}
    <fieldset>
        <legend>Search & Filter Products</legend>
        <form method="GET" action="{{ route('admin.products.index') }}">
            <label>Search:</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name">
            &nbsp;&nbsp;

            <label>Category:</label>
            <select name="category">
                <option value="">-- All Categories --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
            &nbsp;&nbsp;

            <button type="submit">Filter</button>
            <a href="{{ route('admin.products.index') }}">Reset</a>
        </form>
    </fieldset>

    <br>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Seller</th>
                <th>Category / Type</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td align="center">{{ $product->product_id }}</td>
                    <td>
                        <strong>{{ $product->product_name }}</strong>
                    </td>
                    <td>{{ $product->seller->user_name ?? 'N/A' }}</td>
                    <td>
                        {{ $product->product_category }}<br>
                        <small>{{ $product->product_type }}</small>
                    </td>
                    <td align="right">₹{{ number_format($product->product_price, 2) }}</td>
                    <td align="center">
                        @if ($product->product_available_quantity > 0)
                            <span style="color: green;">{{ $product->product_available_quantity }} in stock</span>
                        @else
                            <span style="color: red; font-weight: bold;">Out of Stock</span>
                        @endif
                    </td>
                    <td align="center">
                        <a href="{{ route('products.show', $product->product_id) }}" target="_blank">View Details</a>
                        &nbsp;|&nbsp;
                        <form action="{{ route('admin.products.destroy', $product->product_id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Remove product \'{{ $product->product_name }}\' from the platform?')">
                                Remove
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <div class="admin-pagination">
        {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>

</body>
</html>
