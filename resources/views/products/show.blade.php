<!DOCTYPE html>
<html>
<head>
    <title>{{ $product->product_name }} - Product Details</title>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
</head>
<body class="storefront">
    <header class="site-header">
        <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
        <nav class="site-nav" aria-label="Main navigation">
            <a href="{{ route('products.index') }}">Browse</a>
        @auth
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('cart.index') }}">Cart ({{ auth()->user()->user_cart_quantity }})</a>
        @else
            <a href="{{ route('login') }}">Login</a>
        @endauth
        </nav>
    </header>

    <main class="product-detail">
        <p class="breadcrumbs"><a href="{{ route('products.index') }}">Browse products</a> / {{ $product->product_category }}</p>

    @if (session('success'))
        <p class="notice-success">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p class="notice-error">{{ session('error') }}</p>
    @endif

        <section class="detail-layout">
            <div class="detail-media">
                @if (!empty($product->product_picture))
                    <img src="{{ route('products.picture', $product->product_id) }}" alt="{{ $product->product_name }}">
                @else
                    <span class="product-placeholder">Market find</span>
                @endif
            </div>
            <div class="detail-copy">
                <div class="detail-topline">
                    <span class="eyebrow">{{ $product->product_category }}</span>
                    @if ($product->product_available_quantity > 0)
                        <span class="stock-badge">In Stock ({{ $product->product_available_quantity }} units available)</span>
                    @else
                        <span class="stock-badge is-out">Out of Stock</span>
                    @endif
                </div>
                <h1>{{ $product->product_name }}</h1>
                <p>{{ $product->product_type }} <span aria-hidden="true">·</span> Sold by {{ $product->seller->user_name ?? 'N/A' }}</p>
                <p class="detail-price">₹{{ number_format($product->product_price, 2) }}</p>
                @if ($product->product_available_quantity > 0)
                    <form class="buy-form" method="POST" action="{{ route('cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                        <label for="quantity">Quantity<input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->product_available_quantity }}"></label>
                        <button type="submit">Add to Cart</button>
                    </form>
                @else
                    <p class="notice-error">This product is currently out of stock and cannot be added to the cart.</p>
                @endif
                <table class="detail-table"><tbody>
                    <tr><th scope="row">Category</th><td>{{ $product->product_category }}</td></tr>
                    <tr><th scope="row">Type</th><td>{{ $product->product_type }}</td></tr>
                    <tr><th scope="row">Availability</th><td>{{ $product->product_available_quantity }} available</td></tr>
                    <tr><th scope="row">Seller</th><td>{{ $product->seller->user_name ?? 'N/A' }}</td></tr>
                </tbody></table>
            </div>
        </section>
        <section class="detail-description">
            <h2>About this item</h2>
            <p>{{ $product->product_description ?: 'No detailed description provided.' }}</p>
            @if (!empty($product->product_meta_data))
                <h3>Specifications</h3>
                <pre>{{ is_array($product->product_meta_data) ? json_encode($product->product_meta_data, JSON_PRETTY_PRINT) : $product->product_meta_data }}</pre>
            @endif
        </section>
    </main>
</body>
</html>