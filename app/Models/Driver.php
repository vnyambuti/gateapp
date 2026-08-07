<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Driver extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'driver_id', 'phone_number'];

    public function gateLogs(): HasMany
    {
        return $this->hasMany(GateLog::class);
    }
}
