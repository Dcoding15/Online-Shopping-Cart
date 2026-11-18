<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Edit Profile</title>
</head>
<body>
    @include('partials.app-header')

    <h1>Edit Profile</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color: #a12828;">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="display: flex; max-width: 500px; flex-direction: column;">
        @csrf
        @method('PUT')

        <label>Name</label>
        <input
            type="text"
            name="user_name"
            value="{{ old('user_name', $user->user_name) }}"
            placeholder="Enter name"
            maxlength="100"
            data-name-field
            pattern="[A-Za-z]+"
            title="Name can contain only letters A-Z or a-z"
            required
        >

        <br>

        <label>Phone Number</label>
        <input
            type="text"
            name="phone_no"
            value="{{ old('phone_no', $user->phone_no) }}"
            pattern="[0-9]{10}"
            maxlength="10"
            inputmode="numeric"
            title="Phone number must contain exactly 10 digits"
            required
        >

        <br>

        <label>Address</label>
        <textarea
            name="user_addr"
            maxlength="255"
            data-address-field
            title="Address can contain letters, digits, spaces, commas, hyphens, and parentheses"
        >{{ old('user_addr', $user->user_addr) }}</textarea>

        <br>

        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">

        <br>

        <label>Profile Picture</label>
        <input type="file" name="user_profile_pic" accept="image/jpeg,image/png,image/gif,image/webp">

        @if ($user->user_profile_pic)
            <label class="check-label">
                <input type="checkbox" name="remove_profile_picture" value="1">
                Remove current profile picture
            </label>
        @endif

        <br>

        <label>New Password (leave blank to keep current)</label>
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

        <br>

        <label>Confirm New Password</label>
        <div class="password-field">
            <input type="password" name="password_confirmation" autocomplete="new-password">
            <button type="button" class="password-toggle" data-password-label="password confirmation" aria-label="Show password confirmation" aria-pressed="false">
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

        <button type="submit">Save Changes</button>
        <br>
        <a href="{{ route('dashboard') }}">Cancel</a>
    </form>

    <script src="{{ asset('js/address-validation.js') }}" defer></script>
    <script src="{{ asset('js/name-validation.js') }}" defer></script>
    <script src="{{ asset('js/password-toggle.js') }}" defer></script>
</body>
</html>
