<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_name',
        'subtotal',
        'discount',
        'total',
        'paid_amount',
        'user_id',
        'cashier_name',
        'refunded_at',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * A supermarket's "today" runs 6am to 6am, not midnight to midnight, so a
     * cashier closing out late at night doesn't get tomorrow's early sales
     * mixed into the same till, and vice versa.
     */
    public static function currentBusinessDayStart(): Carbon
    {
        $now = now();
        $todaySixAm = $now->copy()->startOfDay()->addHours(6);

        return $now->gte($todaySixAm) ? $todaySixAm : $todaySixAm->subDay();
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
