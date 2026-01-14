<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'type',
        'color',
        'icon',
        'parent_id',
    ];

    protected $casts = [
        'type' => CategoryType::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public static function booted()
    {
        static::creating(function(self $category) {
            // Always set user_id to authenticated user (except in testing)
            if (app()->environment('testing') && !empty($category->user_id)) {
                // In tests, allow factories to set user_id
                return;
            }
            $category->user_id = auth()->id();
        });
    }
}
