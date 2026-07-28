<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTable extends Model
{
    use HasFactory;
    protected $table = 'tables';
    protected $fillable = ['name', 'area', 'status', 'price_per_hour', 'active'];
    protected $casts = ['price_per_hour' => 'decimal:2', 'active' => 'boolean'];
    public function sessions() { return $this->hasMany(TableSession::class, 'table_id'); }
    public function activeSession() { return $this->sessions()->where('status', 'playing')->latest()->first(); }
}
