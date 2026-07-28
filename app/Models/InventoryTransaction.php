<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'staff_id', 'quantity', 'type', 'note'];
    public function product() { return $this->belongsTo(Product::class); }
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
}
