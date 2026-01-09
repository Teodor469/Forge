<?php

use App\Enums\WalletType;
use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

//!Test index method
test('user can successfully view their own wallets', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Test 1 wallet'
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Test 2 wallet'
    ]);
    $response = $this->actingAs($user1)->getJson('/api/wallet/user-wallets');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all wallets!'])
        ->assertJsonCount(1, 'wallets')
        ->assertJsonFragment(['name' => 'Test 1 wallet'])
        ->assertJsonMissing(['name' => 'Test 2 wallet']);
});

test('guest user is unable to view any wallets because they do not have an account', function() {
    $response = $this->actingAsGuest()->getJson('/api/wallet/user-wallets');

    $response->assertStatus(401)
    ->assertJson(['message' => 'Unauthenticated.']);
});

test('user does not have any wallets but wants to get all wallets', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/wallet/user-wallets');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all wallets!'])
        ->assertJsonCount(0, 'wallets');

});

test('user has multiple wallets and can successfully view their own wallets', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::factory()->count(10)->create([
        'user_id' => $user1->id,
        'name' => 'Test 1 wallet'
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Test 2 wallet'
    ]);
    $response = $this->actingAs($user1)->getJson('/api/wallet/user-wallets');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all wallets!'])
        ->assertJsonCount(10, 'wallets')
        ->assertJsonFragment(['name' => 'Test 1 wallet'])
        ->assertJsonMissing(['name' => 'Test 2 wallet'])
        ->assertJsonStructure([
            'wallets' => [
                '*' => [
                    'id', 'name', 'type', 'balance', 'currency', 'institution', 'last_four_digits', 'is_active'
                ]
            ]
        ]);
});


//!Test store method
test('user can successfully create a new wallet', function() {
    $user = User::factory()->create();

    $walletData = [
        'name' => 'My Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1500.50,
        'currency' => 'EUR',
        'institution' => 'Test Bank',
        'last_four_digits' => '1234',
        'is_active' => true,
    ];

    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', $walletData);

    $response->assertStatus(201)
        ->assertJson(['message' => 'New wallet created!'])
        ->assertJsonStructure([
            'wallet' => [
                'id', 'name', 'type', 'balance', 'currency', 'institution', 'last_four_digits', 'is_active'
            ]
        ]);

    $wallet = Wallet::where('name', 'My Test Wallet')->first();
    expect($wallet)->not->toBeNull();
    expect($wallet->user_id)->toBe($user->id);
    expect($wallet->name)->toBe('My Test Wallet');
});


//!Test show method
test('user can successfully view a single wallet', function() {
    $user = User::factory()->create();

    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test wallet',
    ]);

    $response = $this->actingAs($user)->getJson("/api/wallet/user-wallets/{$wallet->id}");

    $response->assertStatus(200)
    ->assertJsonStructure([
            'wallet' => [
                'id', 'name', 'type', 'balance', 'currency', 'institution', 'last_four_digits', 'is_active'
            ]
        ]);
});


//!Test update method
test('user can successfully update their own wallet', function() {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test wallet',
    ]);

    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'name' => 'Updated test wallet'
    ]);

    $response->assertStatus(201)
    ->assertJson(['message' => 'Successfully updated wallet!'])
    ->assertJsonStructure([
            'wallet' => [
                'id', 'name', 'type', 'balance', 'currency', 'institution', 'last_four_digits', 'is_active'
            ]
        ]);

    $wallet->refresh();
    expect($wallet->name)->toBe('Updated test wallet');
});


//!Test delete method
test('user can successfully delete their own wallet', function() {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Deleted wallet'
    ]);

    $response = $this->actingAs($user)->deleteJson("api/wallet/user-wallets/delete/{$wallet->id}");

    $response->assertStatus(200)
    ->assertJson(['message' => 'Successfully deleted wallet!']);

    assertDatabaseMissing('wallets', ['id' => $wallet->id]);
});
