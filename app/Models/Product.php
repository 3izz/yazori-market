<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'barcode',
        'unit',
        'is_weighted',
        'purchase_price',
        'sale_price',
        'quantity',
        'min_stock',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'quantity' => 'decimal:3',
            'is_weighted' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock > 0 && $this->quantity <= $this->min_stock;
    }

    /**
     * The quantity column is decimal so weighted products can store fractions
     * like 0.250, but that means a whole-unit product's quantity always comes
     * back as e.g. "5.000" - this trims it back to "5" for display while
     * keeping "0.250" as-is.
     */
    public function formattedQuantity(): string
    {
        $trimmed = rtrim(rtrim((string) $this->quantity, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }
}
