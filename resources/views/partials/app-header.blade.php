<header class="site-header">
    <a class="brand" href="{{ route('products.index') }}">
        <span class="brand-mark" aria-hidden="true">m</span>
        <span>Market</span>
    </a>
    @auth
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @endauth
</header>
