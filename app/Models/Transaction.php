<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'fee',
        'status',
        'tx_hash',
        'notes',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:8',
            'fee'          => 'decimal:8',
            'confirmed_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
