<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int $qty
 * @property float $price
 * @property bool $voided
 * @property-read \App\Models\Product|null $product
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'product_id', 'qty', 'price', 'voided'
    ];

    protected $casts = [
        'voided' => 'boolean',
        'price' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Convenience: return whether this sale item is voided.
     */
    public function isVoided(): bool
    {
        return (bool) $this->getAttribute('voided');
    }

    /**
     * Return quantity as int.
     */
    public function getQty(): int
    {
        return (int) $this->getAttribute('qty');
    }

    /**
     * Mark this sale item as voided and persist.
     */
    public function markVoided(): void
    {
        $this->setAttribute('voided', true);
        $this->save();
    }
}
