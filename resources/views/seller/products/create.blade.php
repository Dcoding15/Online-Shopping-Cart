<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Add Product</title>
</head>
<body>

    <h1>Add Product</h1>

    <p>
        <a href="{{ route('seller.dashboard') }}">Dashboard</a> |
        <a href="{{ route('seller.products') }}">My Products</a>
    </p>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label>Product Name:</label><br>
        <input type="text" name="product_name" value="{{ old('product_name') }}" required>
        <br><br>

        <label>Product Category:</label><br>
        <select name="product_category" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" {{ old('product_category') == $category ? 'selected' : '' }}>
                    {{ $category }}
                </option>
            @endforeach
        </select>
        <br><br>

        <label>Product Type:</label><br>
        <select name="product_type" required>
            <option value="">-- Select Type --</option>
            @foreach($types as $type)
                <option value="{{ $type }}" {{ old('product_type') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>
        <br><br>

        <label>Price (₹):</label><br>
        <input type="number" step="0.01" min="0" name="product_price" value="{{ old('product_price') }}" required>
        <br><br>

        <label>Available Quantity:</label><br>
        <input type="number" min="0" name="product_available_quantity" value="{{ old('product_available_quantity', 0) }}" required>
        <br><br>

        <label>Product Picture (optional):</label><br>
        <input type="file" name="product_picture" accept="image/*">
        <br><br>

        <label>Description:</label><br>
        <textarea name="product_description" rows="4" cols="50">{{ old('product_description') }}</textarea>
        <br><br>

        <label>Meta Data (JSON or text, optional):</label><br>
        <textarea name="product_meta_data" rows="3" cols="50">{{ old('product_meta_data') }}</textarea>
        <br><br>

        <button type="submit">Add Product</button>
    </form>

</body>
</html>