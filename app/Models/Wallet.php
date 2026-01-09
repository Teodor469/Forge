<?php

namespace App\Models;

use App\Enums\WalletType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = [
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
            // Always set user_id to authenticated user (except in testing)
            if (app()->environment('testing') && !empty($wallet->user_id)) {
                // In tests, allow factories to set user_id
                return;
            }
            $wallet->user_id = auth()->id();
        });
    }
}
