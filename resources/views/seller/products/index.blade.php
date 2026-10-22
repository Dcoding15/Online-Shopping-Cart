<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>My Products</title>
</head>
<body>

    <h1>My Products</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <p>
        <a href="{{ route('seller.dashboard') }}">Dashboard</a> |
        <a href="{{ route('seller.products.create') }}">Add Product</a>
    </p>

    @if($products->count() > 0)
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Actions</th>
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
                        <td>{{ $product->product_description ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('seller.products.edit', $product->product_id) }}">Edit</a>
                            &nbsp;|&nbsp;
                            <form action="{{ route('seller.products.destroy', $product->product_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>You have not added any products yet.</p>
    @endif

</body>
</html>