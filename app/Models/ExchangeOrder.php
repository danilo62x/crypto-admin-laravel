<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'pair',
        'side',
        'price',
        'amount',
        'total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price'  => 'decimal:8',
            'amount' => 'decimal:8',
            'total'  => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
