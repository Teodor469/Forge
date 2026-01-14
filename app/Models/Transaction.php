<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use Filterable;

    protected $fillable = [
        'wallet_id',
        'category_id',
        'amount',
        'type',
        'merchant',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    //!This is the reference for what filters there are going to be
    protected $filterable = [
        'type' => 'exact',
        'transaction_date_from' => 'date_gte',
        'transaction_date_to' => 'date_lte',
        'amount_min' => 'decimal_min',
        'amount_max' => 'decimal_max',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
