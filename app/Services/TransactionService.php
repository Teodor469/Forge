<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;

class TransactionService {
    public function createTransaction(array $data)
    {
        $wallet = Wallet::where('id', $data['wallet_id'])
                        ->where('user_id', auth()->id())
                        ->firstOrFail();

        $transaction = Transaction::create($data);

        $this->updateWalletBalance($wallet, $transaction);
        //!Need to ensure that if one feature fails both will fail because data won't be consistent

        return $transaction;
    }

    public function updateWalletBalance(Wallet $wallet, Transaction $transaction)
    {
        switch($transaction->type) {
            case TransactionType::Expense:
                $wallet->decrement('balance', $transaction->amount);
                break;
            case TransactionType::Transfer: //! Need the second transaction for the next wallet_id
                $wallet->decrement('balance', $transaction->amount);
                break;
            case TransactionType::Income:
                $wallet->increment('balance', $transaction->amount);
                break;
        }
    }
}