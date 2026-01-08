<?php

namespace App\Models;

use App\WalletType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'type',
        'balance',
        'currency',
        'institution',
        'last_four_digits',
        'is_active'
    ];

    protected $casts = [
        'type' => WalletType::class,
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ]; 

    public static function booted()
    {
        static::creating(function(self $wallet) {
            $wallet->used_id = auth()->id();
        });
    }
}
