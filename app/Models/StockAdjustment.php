<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_before',
        'quantity_after',
        'change',
        'reason',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'change' => 'decimal:3',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
