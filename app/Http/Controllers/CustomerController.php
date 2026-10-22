<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

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
