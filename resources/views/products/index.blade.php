<!DOCTYPE html>
<html>
<head>
    <title>Products Catalog - Buyer Browsing</title>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
</head>
<body class="storefront">
    <header class="site-header">
        <a class="brand" href="{{ route('products.index') }}"><span class="brand-mark" aria-hidden="true">m</span><span>Market</span></a>
        <nav class="site-nav" aria-label="Main navigation">
        @auth
            <span>{{ auth()->user()->user_name }}</span>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('cart.index') }}">Cart ({{ auth()->user()->user_cart_quantity }})</a>
            <a href="{{ route('orders.index') }}">Orders</a>
            @if(auth()->user()->isSeller())<a href="{{ route('seller.dashboard') }}">Seller</a>@endif
            <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Logout</button></form>
        @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Create account</a>
        @endauth
        </nav>
    </header>

    <main class="storefront-main">
        <section class="market-hero">
            <div><span class="eyebrow">A little more worth finding</span><h1>Good things, found here.</h1><p>Thoughtful finds across everyday categories.</p></div>
            <div class="hero-stamp" aria-label="Prices in Indian rupees"><strong>₹</strong><span>Everyday finds</span></div>
        </section>

    @if (session('success'))
        <p class="notice-success">{{ session('success') }}</p>
    @endif

        <section class="filter-panel" aria-labelledby="filter-title">
            <div class="filter-heading"><h2 id="filter-title">Find your next good thing</h2></div>
            <form class="catalog-filters" method="GET" action="{{ route('products.index') }}">
                <label class="filter-field"><span>Search</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Product name or keyword"></label>
                <label class="filter-field"><span>Category</span><select name="category">
                <option value="">-- All Categories --</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
                </select></label>
                <label class="filter-field"><span>Type</span><select name="type">
                <option value="">-- All Types --</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
                </select></label>
                <div class="filter-field"><label for="min-price">Price range</label><div class="price-range">
                    <input id="min-price" type="number" step="0.01" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="Min">
                    <span>to</span>
                    <input type="number" step="0.01" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="Max" aria-label="Maximum price">
                </div></div>
                <label class="filter-field"><span>Sort by</span><select name="sort">
                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A-Z</option>
                </select></label>
                <div class="filter-extras">
                    <label class="check-label"><input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }}>In stock only</label>
                    <div class="filter-actions"><a href="{{ route('products.index') }}">Reset</a><button type="submit">Apply filters</button></div>
                </div>
            </form>
        </section>

        <div class="results-heading"><h2>Browse products</h2><p class="results-count">{{ $products->total() }} product(s)</p></div>

    @if ($products->count() === 0)
        <div class="empty-state"><h2>No products found</h2><p>Try a different search or clear your filters.</p><a href="{{ route('products.index') }}">Clear filters</a></div>
    @else
        <section class="product-grid" aria-label="Product results">
                @foreach ($products as $product)
                    <article class="product-card">
                        <a class="product-visual" href="{{ route('products.show', $product->product_id) }}" aria-label="View {{ $product->product_name }}">
                            @if (!empty($product->product_picture))
                                <img src="{{ route('products.picture', $product->product_id) }}" alt="{{ $product->product_name }}">
                            @else
                                <span class="product-placeholder">Market find</span>
                            @endif
                            @if ($product->product_available_quantity > 0)
                                <span class="stock-badge">In stock</span>
                            @else
                                <span class="stock-badge is-out">Out of stock</span>
                            @endif
                        </a>
                        <div class="product-body">
                            <div class="product-kicker"><span>{{ $product->product_category }}</span><span aria-hidden="true">/</span><span>{{ $product->product_type }}</span></div>
                            <h3 class="product-name"><a href="{{ route('products.show', $product->product_id) }}">{{ $product->product_name }}</a></h3>
                            <p class="product-description">{{ !empty($product->product_description) ? Str::limit($product->product_description, 78) : 'A considered find from '.($product->seller->user_name ?? 'our marketplace').'.' }}</p>
                            <div class="product-footer"><strong class="product-price">₹{{ number_format($product->product_price, 2) }}</strong><a class="product-action" href="{{ route('products.show', $product->product_id) }}">View item <span aria-hidden="true">↗</span></a></div>
                        </div>
                    </article>
                @endforeach
        </section>

        <div class="pagination">
            {{ $products->links() }}
        </div>
    @endif
    </main>
</body>
</html>