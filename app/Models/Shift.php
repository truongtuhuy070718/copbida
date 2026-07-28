<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;
    protected $fillable = ['staff_id', 'started_at', 'ended_at', 'opening_cash', 'closing_cash', 'note'];
    protected $casts = [
        'started_at' => 'datetime', 'ended_at' => 'datetime',
        'opening_cash' => 'decimal:2', 'closing_cash' => 'decimal:2',
    ];
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
}
