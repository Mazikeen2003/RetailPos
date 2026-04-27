<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property float $subtotal
 * @property string $discount
 * @property float $discount_amount
 * @property float $total
 * @property bool $canceled
 * @property string|null $canceled_reason
 * @property bool $reprinted
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SaleItem[] $items
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subtotal', 'discount', 'discount_amount', 'total', 'canceled', 'canceled_reason', 'reprinted'
    ];

    protected $casts = [
        'canceled' => 'boolean',
        'reprinted' => 'boolean',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'total' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
