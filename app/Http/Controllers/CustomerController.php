<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view(
            'dashboard',
            compact('user')
        );
    }

    public function editProfile()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'user_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z]+$/'],
            'phone_no' => ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/', 'unique:users,phone_no,'.$user->user_id.',user_id'],
            'user_addr' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ,\-()]*$/'],
            'date_of_birth' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'user_profile_pic' => ['nullable', 'image', 'max:2048'],
            'remove_profile_picture' => ['nullable', 'boolean'],
        ]);

        $profilePicture = $user->user_profile_pic;

        if ($request->boolean('remove_profile_picture')) {
            $profilePicture = null;
        }

        if ($request->hasFile('user_profile_pic')) {
            $uploadedFile = $request->file('user_profile_pic');

            if (! $uploadedFile instanceof UploadedFile) {
                throw new RuntimeException('Unable to read the uploaded profile picture.');
            }

            $profilePicture = fopen($uploadedFile->getRealPath(), 'rb');

            if ($profilePicture === false) {
                throw new RuntimeException('Unable to read the uploaded profile picture.');
            }
        }

        try {
            $user->update([
                'user_name' => $validated['user_name'],
                'phone_no' => $validated['phone_no'],
                'user_addr' => $validated['user_addr'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'user_profile_pic' => $profilePicture,
            ]);

            if (! empty($validated['password'])) {
                $user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }
        } finally {
            if (is_resource($profilePicture) && $profilePicture !== $user->getRawOriginal('user_profile_pic')) {
                fclose($profilePicture);
            }
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Profile updated successfully.');
    }

    public function picture(): Response
    {
        $picture = auth()->user()->user_profile_pic;

        if (empty($picture)) {
            abort(404);
        }

        $data = $picture;

        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        if (! is_string($data)) {
            abort(415);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($data);

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            abort(415);
        }

        return response($data)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
