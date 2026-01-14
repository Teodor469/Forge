<?php

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

//! Test index method

test('get all transactions for the authenticated user', function () {

});

//! Test transactionsPerCategory method
test('get all transactions for the authenticated user from a specific category', function() {

});

//! Test store method
test('make a transaction check if the transaction has been made and check if the amount has been deducted from the wallet', function() {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create([
        'user_id' => $user->id,
        'balance' => 1000.50,
    ]);
    $category = Category::factory()->create([
        'user_id' => $user->id
    ]);

    $transactionData = [
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => 10.48,
        'type' => TransactionType::Expense->value,
        'merchant' => 'Family Coffee',
        'description' => 'Drank a coffee',
        'transaction_date' => date('Y-m-d'),
    ];

    $response = $this->actingAs($user)->postJson('/api/transaction/user-transactions/store', $transactionData);

    $response->assertStatus(201)
    ->assertJson(['message' => 'Successfully made a transaction!']);

    $this->assertDatabaseHas('transactions', [
        'wallet_id' => $wallet->id,
        'amount' => 10.48,
        'type' => TransactionType::Expense->value,
    ]);

    $wallet->refresh();
    expect($wallet->balance)->toBe("990.02");
});
