<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'category_id', 'unit_id', 'unit', 'price', 'cost', 'stock', 'min_stock', 'track_stock', 'active'];
    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'active' => 'boolean',
        'track_stock' => 'boolean',
    ];
    public function category() { return $this->belongsTo(Category::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
    public function inventoryTransactions() { return $this->hasMany(InventoryTransaction::class); }
}
