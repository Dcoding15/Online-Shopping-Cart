<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Seller Dashboard</title>
</head>
<body>

    <h1>Seller Dashboard</h1>

    <p>Welcome, {{ auth()->user()->user_name ?? 'Seller' }}</p>

    <p>
        <a href="{{ route('seller.products') }}">My Products</a> |
        <a href="{{ route('seller.products.create') }}">Add Product</a>
    </p>

    <h2>Overview</h2>
    <p>Total Products: {{ $products->count() }}</p>
    <p>Wallet Balance: ₹{{ number_format(auth()->user()->user_wallet_amount ?? 0, 2) }}</p>

    <h2>Recent Products</h2>
    @if($products->count() > 0)
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Available Quantity</th>
                    <th>Category</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->product_id }}</td>
                        <td>{{ $product->product_name }}</td>
                        <td>₹{{ number_format($product->product_price, 2) }}</td>
                        <td>{{ $product->product_available_quantity }}</td>
                        <td>{{ $product->product_category }}</td>
                        <td>{{ $product->product_type }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No products found. <a href="{{ route('seller.products.create') }}">Add your first product</a>.</p>
    @endif

</body>
</html>
