<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Edit Seller</title>
</head>
<body>
    @include('partials.app-header')

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
        <input type="text" name="user_name" value="{{ old('user_name', $seller->user_name) }}" placeholder="Enter name" maxlength="100" data-name-field pattern="[A-Za-z]+" title="Name can contain only letters A-Z or a-z" required>
        <br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone_no" value="{{ old('phone_no', $seller->phone_no) }}" pattern="[0-9]{10}" maxlength="10" inputmode="numeric" title="Phone number must contain exactly 10 digits" required>
        <br><br>

        <label>Address:</label><br>
        <input type="text" name="user_addr" value="{{ old('user_addr', $seller->user_addr) }}" maxlength="255" pattern="[A-Za-z0-9 ,\-()]*" title="Address can contain letters, digits, spaces, commas, hyphens, and parentheses">
        <br><br>

        <label>Date of Birth:</label><br>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $seller->date_of_birth ? \Carbon\Carbon::parse($seller->date_of_birth)->format('Y-m-d') : '') }}">
        <br><br>

        <label>New Password (leave blank to keep current):</label><br>
        <div class="password-field">
            <input type="password" name="password" minlength="8" autocomplete="new-password">
            <button type="button" class="password-toggle" data-password-label="password" aria-label="Show password" aria-pressed="false">
                <svg class="eye-open" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                    <circle cx="12" cy="12" r="2.5"></circle>
                </svg>
                <svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 3l18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-2.1 2.7M6.1 6.1C3.8 7.8 2.5 12 2.5 12s3.5 6 9.5 6c1.2 0 2.3-.2 3.3-.6M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
                </svg>
            </button>
        </div>
        <small>If changing it, use at least 8 characters, including uppercase and lowercase letters, a number, and a symbol.</small>
        <br><br>

        <button type="submit">Update Seller</button>
    </form>

</body>
<script src="{{ asset('js/password-toggle.js') }}" defer></script>
<script src="{{ asset('js/name-validation.js') }}" defer></script>
</html>
