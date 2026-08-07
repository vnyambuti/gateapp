<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GateLog extends Model
{
    use HasFactory;
    protected $fillable = ['vehicle_id', 'driver_id', 'time_in', 'time_out', 'gated_in_by', 'gated_out_by'];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
    public function gatedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gated_in_by');
    }
    public function gatedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gated_out_by');
    }
}
