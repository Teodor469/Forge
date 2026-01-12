<?php

use App\Enums\CurrencyType;
use App\Enums\WalletType;
use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
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

    // Create both active and inactive wallets to test that index returns ALL
    $activeWallets = Wallet::factory()->count(5)->create([
        'user_id' => $user1->id,
        'name' => 'Active Test wallet',
        'is_active' => true
    ]);

    $inactiveWallets = Wallet::factory()->count(3)->create([
        'user_id' => $user1->id,
        'name' => 'Inactive Test wallet',
        'is_active' => false
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Test 2 wallet'
    ]);
    
    $response = $this->actingAs($user1)->getJson('/api/wallet/user-wallets');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all wallets!'])
        ->assertJsonCount(8, 'wallets') // 5 active + 3 inactive = 8 total
        ->assertJsonFragment(['name' => 'Active Test wallet'])
        ->assertJsonFragment(['name' => 'Inactive Test wallet'])
        ->assertJsonMissing(['name' => 'Test 2 wallet'])
        ->assertJsonStructure([
            'wallets' => [
                '*' => [
                    'id', 'name', 'type', 'balance', 'currency', 'institution', 'last_four_digits', 'is_active'
                ]
            ]
        ]);
});

//!Test active method
test('user can successfully view their own active wallets', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Test 1 active wallet',
        'is_active' => true
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Test 2 active wallet',
        'is_active' => true
    ]);
    
    $response = $this->actingAs($user1)->getJson('/api/wallet/user-wallets/active');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all active wallets!'])
        ->assertJsonCount(1, 'wallets')
        ->assertJsonFragment(['name' => 'Test 1 active wallet'])
        ->assertJsonMissing(['name' => 'Test 2 active wallet']);
});

test('guest user is unable to view any active wallets because they do not have an account', function() {
    $response = $this->actingAsGuest()->getJson('/api/wallet/user-wallets/active');

    $response->assertStatus(401)
    ->assertJson(['message' => 'Unauthenticated.']);
});

test('user does not have any active wallets but wants to get all active wallets', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/wallet/user-wallets/active');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all active wallets!'])
        ->assertJsonCount(0, 'wallets');
});

test('user has multiple active wallets and can successfully view their own active wallets', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::factory()->count(8)->create([
        'user_id' => $user1->id,
        'name' => 'Test 1 active wallet',
        'is_active' => true
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Test 2 active wallet',
        'is_active' => true
    ]);
    
    $response = $this->actingAs($user1)->getJson('/api/wallet/user-wallets/active');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all active wallets!'])
        ->assertJsonCount(8, 'wallets')
        ->assertJsonFragment(['name' => 'Test 1 active wallet'])
        ->assertJsonMissing(['name' => 'Test 2 active wallet'])
        ->assertJsonStructure([
            'wallets' => [
                '*' => [
                    'id', 'name', 'type', 'balance', 'currency', 'institution', 'last_four_digits', 'is_active'
                ]
            ]
        ]);
});

test('inactive wallets are not included in active wallets response', function() {
    $user = User::factory()->create();

    $activeWallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Active wallet',
        'is_active' => true
    ]);

    $inactiveWallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Inactive wallet',
        'is_active' => false
    ]);

    $response = $this->actingAs($user)->getJson('/api/wallet/user-wallets/active');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'wallets')
        ->assertJsonFragment(['name' => 'Active wallet'])
        ->assertJsonMissing(['name' => 'Inactive wallet']);
});

//!Test archived method
test('user can successfully view their own archived wallets', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Test 1 archived wallet',
        'is_active' => false
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Test 2 archived wallet',
        'is_active' => false
    ]);
    
    $response = $this->actingAs($user1)->getJson('/api/wallet/user-wallets/archived');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all archived wallets!'])
        ->assertJsonCount(1, 'wallets')
        ->assertJsonFragment(['name' => 'Test 1 archived wallet'])
        ->assertJsonMissing(['name' => 'Test 2 archived wallet']);
});

test('guest user is unable to view any archived wallets because they do not have an account', function() {
    $response = $this->actingAsGuest()->getJson('/api/wallet/user-wallets/archived');

    $response->assertStatus(401)
    ->assertJson(['message' => 'Unauthenticated.']);
});

test('user does not have any archived wallets but wants to get all archived wallets', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/wallet/user-wallets/archived');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all archived wallets!'])
        ->assertJsonCount(0, 'wallets');
});

test('user has multiple archived wallets and can successfully view their own archived wallets', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet1 = Wallet::factory()->count(5)->create([
        'user_id' => $user1->id,
        'name' => 'Test 1 archived wallet',
        'is_active' => false
    ]);

    $wallet2 = Wallet::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Test 2 archived wallet',
        'is_active' => false
    ]);
    
    $response = $this->actingAs($user1)->getJson('/api/wallet/user-wallets/archived');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully returned all archived wallets!'])
        ->assertJsonCount(5, 'wallets')
        ->assertJsonFragment(['name' => 'Test 1 archived wallet'])
        ->assertJsonMissing(['name' => 'Test 2 archived wallet'])
        ->assertJsonStructure([
            'wallets' => [
                '*' => [
                    'id', 'name', 'type', 'balance', 'currency', 'institution', 'last_four_digits', 'is_active'
                ]
            ]
        ]);
});

test('active wallets are not included in archived wallets response', function() {
    $user = User::factory()->create();

    $activeWallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Active wallet',
        'is_active' => true
    ]);

    $archivedWallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'name' => 'Archived wallet',
        'is_active' => false
    ]);

    $response = $this->actingAs($user)->getJson('/api/wallet/user-wallets/archived');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'wallets')
        ->assertJsonFragment(['name' => 'Archived wallet'])
        ->assertJsonMissing(['name' => 'Active wallet']);
});


//!Test store method
test('user can successfully create a new wallet', function() {
    $user = User::factory()->create();

    $walletData = [
        'name' => 'My Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1500.50,
        'currency' => CurrencyType::EUR->value,
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

test('user can create wallets with zero balance', function() {
    $user = User::factory()->create();

    $walletData = [
        'name' => 'My Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 0,
        'currency' => CurrencyType::EUR->value,
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
});

test('user cannot create wallet with negative balance', function() {
    $user = User::factory()->create();

    $walletData = [
        'name' => 'My Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => -1,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
        'last_four_digits' => '1234',
        'is_active' => true,
    ];

    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', $walletData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['balance']);
});

test('wallet type enum validation works correctly', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => 'invalid_type',
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('test wallet validation error for name when storing', function() {
    $user = User::factory()->create();

    // Test missing name
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // Test name too short
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'abcd',
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('test wallet validation error for type when storing', function() {
    $user = User::factory()->create();

    // Test missing type
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('test wallet validation error for balance when storing', function() {
    $user = User::factory()->create();

    // Test missing balance
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => WalletType::Savings->value,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['balance']);
});

test('test wallet validation error for currency when storing', function() {
    $user = User::factory()->create();

    // Test missing currency
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'institution' => 'Test Bank',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['currency']);
});

test('test wallet validation error for institution when storing', function() {
    $user = User::factory()->create();

    // Test missing institution
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['institution']);

    // Test institution too short
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'ab',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['institution']);
});

test('test wallet validation error for last_four_digits when storing', function() {
    $user = User::factory()->create();

    // Test invalid length (too short)
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
        'last_four_digits' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_four_digits']);

    // Test invalid length (too long)
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
        'last_four_digits' => '12345',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_four_digits']);
});

test('test wallet validation error for is_active when storing', function() {
    $user = User::factory()->create();

    // Test invalid boolean value
    $response = $this->actingAs($user)->postJson('/api/wallet/user-wallets/store', [
        'name' => 'Test Wallet',
        'type' => WalletType::Savings->value,
        'balance' => 1000,
        'currency' => CurrencyType::EUR->value,
        'institution' => 'Test Bank',
        'is_active' => 'not_boolean',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['is_active']);
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

test('user cannot see another users wallet in show method', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet = Wallet::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Test wallet',
    ]);

    $response = $this->actingAs($user2)->getJson("/api/wallet/user-wallets/{$wallet->id}");

    $response->assertStatus(403)
    ->assertJson(['message' => 'You are unauthorized to perform this action!']);
});

test('user tries to view non-existent wallet', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson("/api/wallet/user-wallets/1");

    $response->assertStatus(404);
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

test('user cannot update another users wallet', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $wallet = Wallet::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Test wallet',
    ]);

    $response = $this->actingAs($user2)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'name' => 'Updated test wallet'
    ]);

    $response->assertStatus(403)
    ->assertJson(['message' => 'You are unauthorized to perform this action!']);

    $wallet->refresh();
    expect($wallet->name)->toBe('Test wallet');
});

test('user tries to update non-existent wallet', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/1", [
        'name' => 'Updated test wallet'
    ]);

    $response->assertStatus(404);
});

test('wallet can be deactivated and reactivated', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    // Deactivate wallet
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'is_active' => false,
    ]);

    $response->assertStatus(201);
    $wallet->refresh();
    expect($wallet->is_active)->toBeFalse();

    // Reactivate wallet
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'is_active' => true,
    ]);

    $response->assertStatus(201);
    $wallet->refresh();
    expect($wallet->is_active)->toBeTrue();
});

test('test wallet validation error for name when updating', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    // Test name too short
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'name' => 'abcd', // Less than 5 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('test wallet validation error for type when updating', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    // Test invalid enum type
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'type' => 'invalid_type',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('test wallet validation error for balance when updating', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    // Test negative balance
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'balance' => -100,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['balance']);
});

test('test wallet validation error for currency when updating', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    // Test invalid currency enum
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'currency' => 'INVALID',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['currency']);
});

test('test wallet validation error for institution when updating', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    // Test institution too short
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'institution' => 'ab', // Less than 3 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['institution']);
});

test('test wallet validation error for last_four_digits when updating', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    // Test invalid length (too short)
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'last_four_digits' => '123', // Less than 4 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_four_digits']);

    // Test invalid length (too long)
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'last_four_digits' => '12345', // More than 4 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_four_digits']);
});

test('test wallet validation error for is_active when updating', function() {
    $user = User::factory()->create();
    
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
    ]);

    // Test invalid boolean value
    $response = $this->actingAs($user)->putJson("/api/wallet/user-wallets/update/{$wallet->id}", [
        'is_active' => 'not_boolean',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['is_active']);
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

test('user cannot delete another users wallet', function() {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $wallet = Wallet::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Deleted wallet'
    ]);

    $response = $this->actingAs($user2)->deleteJson("api/wallet/user-wallets/delete/{$wallet->id}");

    $response->assertStatus(403)
    ->assertJson(['message' => 'You are unauthorized to perform this action!']);

    assertDatabaseHas('wallets', ['id' => $wallet->id]);
});

test('user tries to delete non-existent wallet', function() {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("api/wallet/user-wallets/delete/1");

    $response->assertStatus(404);
});
