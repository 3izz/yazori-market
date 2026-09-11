<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReset extends Model
{
    protected $fillable = [
        'counted_amount',
        'left_in_drawer',
        'cashier_name',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'counted_amount' => 'decimal:2',
            'left_in_drawer' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
