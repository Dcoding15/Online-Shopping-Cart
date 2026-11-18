<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Login</title>
</head>

<body>
@include('partials.app-header')
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
            <i class="fa-solid fa-eye eye-open" aria-hidden="true"></i>
            <i class="fa-solid fa-eye-slash eye-off" aria-hidden="true"></i>
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