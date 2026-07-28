<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = ['payable_id', 'payable_type', 'staff_id', 'amount', 'method', 'note', 'paid_at'];
    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime'];
    public function payable() { return $this->morphTo(); }
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
}
