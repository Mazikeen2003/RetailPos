<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'cashier_id',
        'subtotal',
        'discount_type',
        'discount_rate',
        'discount_amount',
        'vatable_sales',
        'vat_amount',
        'total',
        'status',
        'reprinted',
        'voided_by_id',
        'void_reason',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'vatable_sales' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'reprinted' => 'boolean',
            'voided_at' => 'datetime',
        ];
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by_id');
    }
}
