<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
test('user is able to reset their forgotten password', function() {
    // User already exists
    $user = User::factory()->create();
    
});
