<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;

uses(DatabaseTransactions::class);

test('registration creates an account using its phone number without an email', function () {
    $phone = '999'.rand(1000000, 9999999);

    $response = $this->post(route('register'), [
        'user_name' => 'Registered Buyer',
        'phone_no' => $phone,
        'user_type' => 'buyer',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', [
        'user_name' => 'Registered Buyer',
        'phone_no' => $phone,
        'user_type' => 'buyer',
    ]);
    $this->assertAuthenticatedAs(User::where('phone_no', $phone)->first());
});

test('registration displays and serves an uploaded profile picture on the dashboard', function () {
    $picture = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jD7sAAAAASUVORK5CYII=');
    $phone = '999'.rand(1000000, 9999999);
    $this->post(route('register'), [
        'user_name' => 'Buyer with photo',
        'phone_no' => $phone,
        'user_type' => 'buyer',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'user_profile_pic' => UploadedFile::fake()->createWithContent('avatar.png', $picture),
    ])->assertRedirect('/dashboard');

    $storedPicture = User::where('phone_no', $phone)->firstOrFail()->user_profile_pic;
    $storedPicture = is_resource($storedPicture) ? stream_get_contents($storedPicture) : $storedPicture;
    expect($storedPicture)->toBe($picture);

    $this->actingAs(User::where('phone_no', $phone)->firstOrFail());

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('profile.picture'), false);
    $this->get(route('profile.picture'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertContent($picture);
});

test('dashboard shows a placeholder when no profile picture is uploaded', function () {
    $user = User::factory()->buyer()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('No profile picture');
    $response->assertDontSee(route('profile.picture'), false);
});

test('profile picture is not available to guests or users without a picture', function () {
    $this->get(route('profile.picture'))->assertRedirect(route('login'));

    $user = User::factory()->buyer()->create();

    $this->actingAs($user)->get(route('profile.picture'))->assertNotFound();
});
