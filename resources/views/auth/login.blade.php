<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Login</title>
</head>

<body>
<div style="max-width: 400px; margin: 12% auto; padding: 20px;">
<h1>Login</h1>

@if ($errors->any())

    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>

@endif

<form method="POST" action="/login" style="display: flex; flex-direction: column;">

    @csrf
    <label>Phone Number</label>

    <input
        type="text"
        name="phone_no"
        value="{{ old('phone_no') }}"
        pattern="[0-9]{10}"
        maxlength="10"
        inputmode="numeric"
        title="Phone number must contain exactly 10 digits"
        required
    >

    <br>

    <label>Password</label>

    <div class="password-field">
        <input
            type="password"
            name="password"
            required
        >
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

    <br>

    <button type="submit">
        Login
    </button>

</form>

<br>

<div style="display: flex; justify-content: space-between; align-items: center;">
<label>Don't have an account?</label>
<a href="/register">
    Create account
</a>
</div>

</div>

</body>
<script src="{{ asset('js/password-toggle.js') }}" defer></script>
</html>