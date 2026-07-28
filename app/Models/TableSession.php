<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    use HasFactory;
    protected $fillable = [
        'table_id', 'staff_id', 'started_at', 'ended_at', 'duration_minutes',
        'hourly_rate', 'table_amount', 'products_amount', 'discount', 'total_amount', 'status'
    ];
    protected $casts = [
        'started_at' => 'datetime', 'ended_at' => 'datetime',
        'hourly_rate' => 'decimal:2', 'table_amount' => 'decimal:2',
        'products_amount' => 'decimal:2', 'discount' => 'decimal:2', 'total_amount' => 'decimal:2',
    ];
    public function table() { return $this->belongsTo(GameTable::class, 'table_id'); }
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
    public function orders() { return $this->hasMany(Order::class); }
    public function payments() { return $this->morphToMany(Payment::class, 'payable'); }
}
