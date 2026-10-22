<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthController extends Controller
{
    public function showRegister()
    {

        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:100',
            'phone_no' => 'required|string|max:150|unique:users,phone_no',
            'user_addr' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'user_type' => 'required|in:seller,buyer',
            'password' => 'required|min:6|confirmed',
            'user_profile_pic' => 'nullable|image|max:2048',
        ]);

        $profilePicture = null;

        if ($request->hasFile('user_profile_pic')) {
            $profilePicture = fopen($request->file('user_profile_pic')->getRealPath(), 'rb');

            if ($profilePicture === false) {
                throw new RuntimeException('Unable to read the uploaded profile picture.');
            }
        }

        try {
            $user = User::create([
                'user_name' => $request->user_name,
                'phone_no' => $request->phone_no,
                'user_addr' => $request->user_addr,
                'user_profile_pic' => $profilePicture,
                'date_of_birth' => $request->date_of_birth,
                'user_type' => $request->user_type,
                'user_cart_quantity' => 0,
                'userpayment_confirmation' => false,
                'user_wallet_amount' => 0,
                'password' => Hash::make($request->password),
            ]);
        } finally {
            if (is_resource($profilePicture)) {
                fclose($profilePicture);
            }
        }

        Auth::login($user);

        return redirect('/dashboard');
    }

    public function showLogin()
    {

        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone_no' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::attempt([
            'phone_no' => $credentials['phone_no'],
            'password' => $credentials['password'],
        ])) {

            $request->session()->regenerate();

            return redirect('/dashboard');
        }
        else {

        }

        return back()->withErrors([
            'phone_no' => 'Invalid phone number or password.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
