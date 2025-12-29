<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

//! Tests for register
test('user registers successfully', function() {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Teo',
        'email' => 'teodor.todorov469@gmail.com',
        'password' => 'SecurePass123',
        'password_confirmation' => 'SecurePass123',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'email' => 'teodor.todorov469@gmail.com'
    ]);

});

test('user password is too short', function() {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Teo',
        'email' => 'teodor.todorov469@gmail.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertStatus(422)
                ->assertJsonValidationErrors('password');

    $this->assertDatabaseMissing('users', [
        'email' => 'teodor.todorov469@gmail.com'
    ]);
});

test('user name is too long', function() {
    $response = $this->postJson('api/auth/register', [
        'name' => 'TeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeoTeo',
        'email' => 'teodor.todorov469@gmail.com',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]);

    $response->assertStatus(422)
                ->assertJsonValidationErrors('name');

    $this->assertDatabaseMissing('users', [
        'email' => 'teodor.todorov469@gmail.com'
    ]);
});

test('user trying to use already existing email', function() {
    User::factory()->create(['email' => 'teodor.todorov469@gmail.com']);

    $response = $this->postJson('api/auth/register', [
        'name' => 'Teo',
        'email' => 'teodor.todorov469@gmail.com',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]);

    $response->assertStatus(422)
                ->assertJsonValidationErrors('email');

    expect(User::where('email', 'teodor.todorov469@gmail.com')->count())->toBe(1);
});

test('user not filling name field in form', function() {
    $response = $this->postJson('api/auth/register', [
        'name' => '',
        'email' => 'teodor.todorov469@gmail.com',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]);

    $response->assertStatus(422)
                ->assertJsonValidationErrors('name');

    $this->assertDatabaseMissing('users', [
        'email' => 'teodor.todorov469@gmail.com'
    ]);
});

test('user not filling email field in form', function() {
    $response = $this->postJson('api/auth/register', [
        'name' => 'Teo',
        'email' => '',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]);

    $response->assertStatus(422)
                ->assertJsonValidationErrors('email');

    $this->assertDatabaseMissing('users', [
        'email' => 'teodor.todorov469@gmail.com'
    ]);
});

test('user not filling password field in form', function() {
    $response = $this->postJson('api/auth/register', [
        'name' => 'Teo',
        'email' => 'teodor.todorov469@gmail.com',
        'password' => '',
        'password_confirmation' => '12345678',
    ]);

    $response->assertStatus(422)
                ->assertJsonValidationErrors('password');

    $this->assertDatabaseMissing('users', [
        'email' => 'teodor.todorov469@gmail.com'
    ]);
});

test('user not filling password confirmation field in form or the confirmation field does not match', function() {
    $response = $this->postJson('api/auth/register', [
        'name' => 'Teo',
        'email' => 'teodor.todorov469@gmail.com',
        'password' => '12345678',
        'password_confirmation' => '',
    ]);

    $response->assertStatus(422)
                ->assertJsonValidationErrors('password');

    $this->assertDatabaseMissing('users', [
        'email' => 'teodor.todorov469@gmail.com'
    ]);
});

//! Tests for login
test('user is able to login with correct credentials and receive token', function() {
    $user = User::factory()->create(['email' => 'teo@mail.com', 'password' => bcrypt('12345678'),]);

    $response = $this->postJson('api/auth/login', [
        'email' => 'teo@mail.com',
        'password' => '12345678'
    ]);

    $response->assertStatus(200)
            ->assertJsonStructure(['token']);

    $token = $response->json('token');

    expect($token)->not->toBeNull();

    $this->assertAuthenticated();

    $this->assertAuthenticatedAs($user);
});

test('user enters wrong password upon login', function() {
    User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => bcrypt('12345678')
    ]);

    $response = $this->postJson('api/auth/login', [
        'email' => 'teo@mail.com',
        'password' => '123456789',
    ]);

    $response->assertStatus(401)
                ->assertJsonMissing(['token']);
});

test('user enters wrong email upon login', function() {
    User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => bcrypt('12345678')
    ]);

    $response = $this->postJson('api/auth/login', [
        'email' => 'teo1@mail.com',
        'password' => '12345678',
    ]);

    $response->assertStatus(401)
            ->assertJsonMissing(['token']);
});

test('email field is not filled out by user upon login', function() {
    User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => bcrypt('12345678')
    ]);

    $response = $this->postJson('api/auth/login', [
        'email' => '',
        'password' => '12345678',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors('email')
            ->assertJsonMissing(['token']);
});

test('password field is not filled out by user upon login', function() {
    User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => bcrypt('12345678')
    ]);

    $response = $this->postJson('api/auth/login', [
        'email' => 'teo@mail.com',
        'password' => '',
    ]);

    $response->assertStatus(422)
            ->assertJsonValidationErrors('password')
            ->assertJsonMissing(['token']);
});

//! Tests for logout
test('user is able to logout', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
            ->postJson('api/auth/logout');

    $response->assertStatus(200);

    expect($user->tokens()->count())->toBe(0);
});

//! Tests for forgotten password
test('user is able to request a password reset if they have forgotten their password', function() {
    // User already exists
    $user = User::factory()->create(['email' => 'teodor.todoro469@gmail.com']);

    Notification::fake();

    $response = $this->postJson('api/auth/forgot-password', [
        'email' => 'teodor.todoro469@gmail.com',
    ]);

    $response->assertStatus(200)
    ->assertJson([
        'message' => 'Reset link sent to your email'
    ]);

    Notification::assertSentTo(
        $user,
        ResetPasswordNotification::class
    );
});

test('user tried to provide a non-existent email for password reset', function() {
    Notification::fake();

    $response = $this->postJson('api/auth/forgot-password', [
        'email' => 'nonexisten@mail.com',
    ]);

    $response->assertStatus(400)
    ->assertJson([
        'message' => "We can't find a user with that email address.",
    ]);

    Notification::assertNothingSent();
});

test('user did not provide an email for the password reset form', function() {
    Notification::fake();

    $response = $this->postJson('api/auth/forgot-password', [
        'email' => '',
    ]);

    $response->assertStatus(422)
    ->assertJsonValidationErrors(['email']);

    Notification::assertNothingSent();
});

test('user is able to update their password after following the reset link from their email', function() {
    $user = User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => Hash::make('oldpassword')
    ]);

    $token = Password::createToken($user);

    $response = $this->postJson('api/auth/reset-password', [
        'token' => $token,
        'email' => 'teo@mail.com',
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successful password reset']);
    
    $user->refresh();

    expect(Hash::check('12345678', $user->password))->toBeTrue();
    expect(Hash::check('oldpassword', $user->password))->toBeFalse();
});

test('user does not have a token for resetting their password', function() {
    $user = User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => Hash::make('oldpassword'),
    ]);

    $response = $this->postJson('api/auth/reset-password', [
        'token' => '',
        'email' => 'teo@mail.com',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token']);

    expect(Hash::check('12345678', $user->password))->toBeFalse();
    expect(Hash::check('oldpassword', $user->password))->toBeTrue();
});

test('user does not provide a valid email address', function() {
    $user = User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => 'oldpassword',
    ]);

    $token = Password::createToken($user);

    $response = $this->postJson('api/auth/reset-password', [
        'token' => $token,
        'email' => 'teo1@mail.com',
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => "We can't find a user with that email address."
        ]);

    expect(Hash::check('12345678', $user->password))->toBeFalse();
    expect(Hash::check('oldpassword', $user->password))->toBeTrue();
});

test('user password does not match', function() {
    $user = User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => 'oldpassword',
    ]);

    $token = Password::createToken($user);

    $response = $this->postJson('api/auth/reset-password', [
        'token' => $token,
        'email' => 'teo1@mail.com',
        'password' => '12345678',
        'password_confirmation' => '12345679'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    expect(Hash::check('12345678', $user->password))->toBeFalse();
    expect(Hash::check('12345679', $user->password))->toBeFalse();
    expect(Hash::check('oldpassword', $user->password))->toBeTrue();
});

test('New password is too short', function() {
    $user = User::factory()->create([
        'email' => 'teo@mail.com',
        'password' => 'oldpassword',
    ]);

    $token = Password::createToken($user);

    $response = $this->postJson('api/auth/reset-password', [
        'token' => $token,
        'email' => 'teo@mail.com',
        'password' => '1234567',
        'password_confirmation' => '1234567'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    expect(Hash::check('1234567', $user->password))->toBeFalse();
    expect(Hash::check('oldpassword', $user->password))->toBeTrue();
});
