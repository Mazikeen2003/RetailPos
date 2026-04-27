<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode', 'name', 'category', 'price', 'stock', 'active',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'active' => 'boolean',
    ];

        /**
         * @property int $id
         * @property string $barcode
         * @property string $name
         * @property string|null $category
         * @property float $price
         * @property int $stock
         * @property bool $active
         * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SaleItem[] $saleItems
         * @mixin \Illuminate\Database\Eloquent\Model
         */

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
