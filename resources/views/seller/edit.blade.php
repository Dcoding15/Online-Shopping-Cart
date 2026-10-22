<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Edit Seller</title>
</head>
<body>

    <h1>Edit Seller</h1>

    <p>
        <a href="{{ route('seller.index') }}">Back to Seller List</a>
    </p>

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('seller.update', $seller->user_id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Seller ID:</label><br>
        <input type="text" value="{{ $seller->user_id }}" disabled>
        <br><br>

        <label>Seller Name:</label><br>
        <input type="text" name="user_name" value="{{ old('user_name', $seller->user_name) }}" required>
        <br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone_no" value="{{ old('phone_no', $seller->phone_no) }}" required>
        <br><br>

        <label>Address:</label><br>
        <input type="text" name="user_addr" value="{{ old('user_addr', $seller->user_addr) }}">
        <br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $seller->date_of_birth ? \Carbon\Carbon::parse($seller->date_of_birth)->format('Y-m-d') : '') }}">
        <br><br>

        <label>Wallet Amount:</label><br>
        <input type="number" step="0.01" name="user_wallet_amount" value="{{ old('user_wallet_amount', $seller->user_wallet_amount) }}">
        <br><br>

        <label>New Password (leave blank to keep current):</label><br>
        <input type="password" name="password">
        <br><br>

        <button type="submit">Update Seller</button>
    </form>

</body>
</html>
