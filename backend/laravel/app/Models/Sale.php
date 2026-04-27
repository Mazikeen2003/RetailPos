<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model {
    protected $fillable = ['transaction_number', 'user_id', 'subtotal', 'discount_amount', 'discount_type', 'total_amount', 'cash_received', 'change_amount'];
    public function items() { return $this->hasMany(SaleItem::class); }
}
