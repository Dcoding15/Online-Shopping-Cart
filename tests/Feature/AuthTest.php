<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
