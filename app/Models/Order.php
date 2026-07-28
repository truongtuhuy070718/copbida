<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_code', 'staff_id', 'table_session_id', 'customer_id',
        'subtotal', 'discount', 'total', 'status', 'payment_method', 'paid_at'
    ];
    protected $casts = [
        'subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'total' => 'decimal:2', 'paid_at' => 'datetime',
    ];
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
    public function tableSession() { return $this->belongsTo(TableSession::class, 'table_session_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->morphToMany(Payment::class, 'payable'); }
}
