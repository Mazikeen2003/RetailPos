<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode', 'name', 'category', 'price', 'stock', 'active',
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
