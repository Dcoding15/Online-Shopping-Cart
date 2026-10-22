<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Register</title>
</head>

<body>
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
        required
    >

    <br>

    <label>Phone</label>
    <input
        type="text"
        name="phone_no"
        value="{{ old('phone_no') }}"
        required
    >

    <br>

    <label>Address</label>
    <textarea name="user_addr"></textarea>

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
    <input
        type="password"
        name="password"
        required
    >

    <br>

    <label>Confirm Password</label>
    <input
        type="password"
        name="password_confirmation"
        required
    >

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
</html>