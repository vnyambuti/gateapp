<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;
    protected $fillable = ['vehicle_number', 'make', 'model', 'color'];

    public function gateLogs(): HasMany
    {
        return $this->hasMany(GateLog::class);
    }

    public function scopeCurrentlyGatedIn(Builder $query): Builder
    {
        return $query->whereHas('gateLogs', fn($q) => $q->whereNull('time_out'));
    }
}
