<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Seller List</title>
</head>
<body>

    <h1>Seller List</h1>

    {{-- Session messages --}}
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    <p>
        <a href="{{ route('seller.create') }}">Add Seller</a> |
        <a href="{{ route('seller.dashboard') }}">Seller Dashboard</a> |
        <a href="{{ route('products.index') }}">Public Products</a>
    </p>

    @if($sellers->count() > 0)
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Date of Birth</th>
                    <th>Wallet Balance</th>
                    <th>Total Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sellers as $seller)
                    <tr>
                        <td>{{ $seller->user_id }}</td>
                        <td>{{ $seller->user_name }}</td>
                        <td>{{ $seller->phone_no }}</td>
                        <td>{{ $seller->user_addr ?? 'N/A' }}</td>
                        <td>{{ $seller->date_of_birth ? \Carbon\Carbon::parse($seller->date_of_birth)->format('Y-m-d') : 'N/A' }}</td>
                        <td>{{ number_format($seller->user_wallet_amount, 2) }}</td>
                        <td>{{ $seller->products_count }}</td>
                        <td>
                            <a href="{{ route('seller.edit', $seller->user_id) }}">Edit</a>
                            &nbsp;|&nbsp;
                            <form action="{{ route('seller.destroy', $seller->user_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this seller?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No sellers found.</p>
    @endif

</body>
</html>
