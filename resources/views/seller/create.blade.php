<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Add Seller</title>
</head>
<body>

    <h1>Add New Seller</h1>

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

    <form action="{{ route('seller.store') }}" method="POST">
        @csrf

        <label>Seller Name:</label><br>
        <input type="text" name="user_name" value="{{ old('user_name') }}" required>
        <br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone_no" value="{{ old('phone_no') }}" required>
        <br><br>

        <label>Address:</label><br>
        <input type="text" name="user_addr" value="{{ old('user_addr') }}">
        <br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
        <br><br>

        <label>Wallet Amount:</label><br>
        <input type="number" step="0.01" name="user_wallet_amount" value="{{ old('user_wallet_amount', '0.00') }}">
        <br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required>
        <br><br>

        <button type="submit">Create Seller</button>
    </form>

</body>
</html>
