<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Edit User #{{ $user->user_id }} - Admin</title>
</head>
<body>
    @include('partials.app-header')

    <p>
        <a href="{{ route('admin.users.index') }}">← Back to Users List</a> |
        <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
    </p>

    <hr>

    <h1>Edit User: {{ $user->user_name }}</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->user_id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>User ID:</label><br>
        <input type="text" value="{{ $user->user_id }}" disabled><br><br>

        <label>Name:</label><br>
        <input type="text" name="user_name" value="{{ old('user_name', $user->user_name) }}" placeholder="Enter name" maxlength="100" data-name-field pattern="[A-Za-z]+" title="Name can contain only letters A-Z or a-z" required><br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone_no" value="{{ old('phone_no', $user->phone_no) }}" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" title="Phone number must contain exactly 10 digits" required><br><br>

        <label>User Role (Type):</label><br>
        <select name="user_type" required>
            <option value="admin" {{ old('user_type', $user->user_type) === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="seller" {{ old('user_type', $user->user_type) === 'seller' ? 'selected' : '' }}>Seller</option>
            <option value="buyer" {{ old('user_type', $user->user_type) === 'buyer' ? 'selected' : '' }}>Buyer</option>
        </select><br><br>

        <label>Address:</label><br>
        <input type="text" name="user_addr" value="{{ old('user_addr', $user->user_addr) }}" maxlength="255" pattern="[A-Za-z0-9 ,\-()]*" title="Address can contain letters, digits, spaces, commas, hyphens, and parentheses"><br><br>

        <button type="submit">Save Changes</button>
    </form>

</body>
<script src="{{ asset('js/name-validation.js') }}" defer></script>
</html>
