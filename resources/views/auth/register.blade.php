<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Register</title>
</head>

<body>
@include('partials.app-header')
<div style="max-width: 500px; margin: 0% auto;">
<h1>Register</h1>

@if ($errors->any())

    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>

@endif

<form
    method="POST"
    action="/register"
    enctype="multipart/form-data"
    style="display: flex; flex-direction: column;"
>

    @csrf

    <label>Name</label>
    <input
        type="text"
        name="user_name"
        placeholder="Enter name"
        maxlength="100"
        data-name-field
        pattern="[A-Za-z]+"
        title="Name can contain only letters A-Z or a-z"
        required
    >

    <br>

    <label>Phone</label>
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

    <label>Address</label>
    <textarea name="user_addr" maxlength="255" data-address-field title="Address can contain letters, digits, spaces, commas, hyphens, and parentheses"></textarea>

    <br>

    <label>Date of Birth</label>
    <input
        type="date"
        name="date_of_birth"
    >

    <br>

    <label>Profile Picture</label>
    <input
        type="file"
        name="user_profile_pic"
        accept="image/*"
    >

    <br>

    <label>User Type</label>

    <select name="user_type" required>

        <option value="buyer">
            Buyer
        </option>

        <option value="seller">
            Seller
        </option>

    </select>

    <br>

    <label>Password</label>
    <div class="password-field">
        <input
            type="password"
            name="password"
            minlength="8"
            autocomplete="new-password"
            required
        >
        <button type="button" class="password-toggle" data-password-label="password" aria-label="Show password" aria-pressed="false">
            <i class="fa-solid fa-eye eye-open" aria-hidden="true"></i>
            <i class="fa-solid fa-eye-slash eye-off" aria-hidden="true"></i>
        </button>
    </div>
    <small>Use at least 8 characters, including uppercase and lowercase letters, a number, and a symbol.</small>

    <br>

    <label>Confirm Password</label>
    <div class="password-field">
        <input
            type="password"
            name="password_confirmation"
            required
        >
        <button type="button" class="password-toggle" data-password-label="password confirmation" aria-label="Show password confirmation" aria-pressed="false">
            <i class="fa-solid fa-eye eye-open" aria-hidden="true"></i>
            <i class="fa-solid fa-eye-slash eye-off" aria-hidden="true"></i>
        </button>
    </div>

    <br>

    <button type="submit">
        Register
    </button>

</form>

<br>

<div style="display: flex; justify-content: space-between; align-items: center;">
<label>Already have an account?</label>
<a href="/login">
    Login
</a>
</div>

</div>

</body>
<script src="{{ asset('js/address-validation.js') }}" defer></script>
<script src="{{ asset('js/name-validation.js') }}" defer></script>
<script src="{{ asset('js/password-toggle.js') }}" defer></script>
</html>