<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;

uses(DatabaseTransactions::class);

test('registration creates an account using its phone number without an email', function () {
    $phone = '999'.rand(1000000, 9999999);

    $response = $this->post(route('register'), [
        'user_name' => 'RegisteredBuyer',
        'phone_no' => $phone,
        'user_type' => 'buyer',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', [
        'user_name' => 'RegisteredBuyer',
        'phone_no' => $phone,
        'user_type' => 'buyer',
    ]);
    $this->assertAuthenticatedAs(User::where('phone_no', $phone)->first());
});

test('registration rejects passwords without the required strength', function () {
    $phone = '999'.rand(1000000, 9999999);

    $this->post(route('register'), [
        'user_name' => 'WeakPasswordBuyer',
        'phone_no' => $phone,
        'user_type' => 'buyer',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertSessionHasErrors('password');

    $this->assertDatabaseMissing('users', ['phone_no' => $phone]);
});

test('registration rejects names containing characters outside a-z and A-Z', function () {
    $phone = '999'.rand(1000000, 9999999);

    $this->post(route('register'), [
        'user_name' => 'Buyer123!',
        'phone_no' => $phone,
        'user_type' => 'buyer',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
    ])->assertSessionHasErrors('user_name');

    $this->assertDatabaseMissing('users', ['phone_no' => $phone]);
});

test('registration rejects phone numbers without exactly 10 digits', function () {
    $phone = '12345';

    $this->post(route('register'), [
        'user_name' => 'Buyer',
        'phone_no' => $phone,
        'user_type' => 'buyer',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
    ])->assertSessionHasErrors('phone_no');

    $this->assertDatabaseMissing('users', ['phone_no' => $phone]);
});

test('registration rejects addresses containing unsupported characters', function () {
    $phone = '999'.rand(1000000, 9999999);

    $this->post(route('register'), [
        'user_name' => 'Buyer',
        'phone_no' => $phone,
        'user_addr' => '123 Main Street @ Downtown',
        'user_type' => 'buyer',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
    ])->assertSessionHasErrors('user_addr');

    $this->assertDatabaseMissing('users', ['phone_no' => $phone]);
});

test('registration displays and serves an uploaded profile picture on the dashboard', function () {
    $picture = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jD7sAAAAASUVORK5CYII=');
    $phone = '999'.rand(1000000, 9999999);
    $this->post(route('register'), [
        'user_name' => 'BuyerWithPhoto',
        'phone_no' => $phone,
        'user_type' => 'buyer',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',
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

test('authenticated users can edit their profile', function () {
    $user = User::factory()->buyer()->create([
        'user_name' => 'OriginalBuyer',
        'phone_no' => '9991000001',
        'user_addr' => '123 Old Street',
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'user_name' => 'UpdatedBuyer',
        'phone_no' => '9991000002',
        'user_addr' => '456 New (Road)',
        'date_of_birth' => '1995-06-15',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success');

    $user->refresh();
    expect($user->user_name)->toBe('UpdatedBuyer')
        ->and($user->phone_no)->toBe('9991000002')
        ->and($user->user_addr)->toBe('456 New (Road)')
        ->and($user->date_of_birth?->format('Y-m-d'))->toBe('1995-06-15');
});

test('profile validation rejects invalid name and phone values', function () {
    $user = User::factory()->buyer()->create([
        'phone_no' => '9991000011',
    ]);

    $this->actingAs($user)->put(route('profile.update'), [
        'user_name' => 'Invalid Name123',
        'phone_no' => '123',
        'user_addr' => 'Address @ Invalid',
    ])
        ->assertSessionHasErrors(['user_name', 'phone_no', 'user_addr']);

    $this->assertDatabaseHas('users', [
        'user_id' => $user->user_id,
        'user_name' => $user->user_name,
        'phone_no' => '9991000011',
    ]);
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
