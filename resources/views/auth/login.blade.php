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
        required
    >

    <br>

    <label>Password</label>

    <input
        type="password"
        name="password"
        required
    >

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
</html>