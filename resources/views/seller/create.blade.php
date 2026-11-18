<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Add Seller</title>
</head>
<body>
    @include('partials.app-header')

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
        <input type="text" name="user_name" value="{{ old('user_name') }}" placeholder="Enter name" maxlength="100" data-name-field pattern="[A-Za-z]+" title="Name can contain only letters A-Z or a-z" required>
        <br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone_no" value="{{ old('phone_no') }}" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" title="Phone number must contain exactly 10 digits" required>
        <br><br>

        <label>Address:</label><br>
        <input type="text" name="user_addr" value="{{ old('user_addr') }}" maxlength="255" pattern="[A-Za-z0-9 ,\-()]*" title="Address can contain letters, digits, spaces, commas, hyphens, and parentheses">
        <br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
        <br><br>

        <label>Password:</label><br>
        <div class="password-field">
            <input type="password" name="password" minlength="8" autocomplete="new-password" required>
            <button type="button" class="password-toggle" data-password-label="password" aria-label="Show password" aria-pressed="false">
                <i class="fa-solid fa-eye eye-open" aria-hidden="true"></i>
                <i class="fa-solid fa-eye-slash eye-off" aria-hidden="true"></i>
            </button>
        </div>
        <small>Use at least 8 characters, including uppercase and lowercase letters, a number, and a symbol.</small>
        <br><br>

        <button type="submit">Create Seller</button>
    </form>

</body>
<script src="{{ asset('js/password-toggle.js') }}" defer></script>
<script src="{{ asset('js/name-validation.js') }}" defer></script>
</html>
