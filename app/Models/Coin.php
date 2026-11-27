<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'symbol',
        'name',
        'price',
        'description',
        'logo_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'     => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }
}
