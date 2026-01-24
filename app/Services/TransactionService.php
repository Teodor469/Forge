<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService {
    public function createTransaction(array $data)
    {
        return DB::transaction(function() use ($data) {
            $wallet = Wallet::where('id', $data['wallet_id'])
                            ->where('user_id', auth()->id())
                            ->firstOrFail();
    
            $transaction = Transaction::create($data);
    
            $this->updateWalletBalance($wallet, $transaction);
    
            return $transaction;
        });
    }

    public function createTransactionFromCsv($data)
    {
        //? Find a way to extract the columns from the csv and match them to the json request
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