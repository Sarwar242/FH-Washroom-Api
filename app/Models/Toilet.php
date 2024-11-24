<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Toilet extends Model
{
    protected $fillable = [
        'washroom_id',
        'number',
        'is_occupied',
        'occupied_by',
        'occupied_at',
        'occupation_expires_at',
        'is_operational'
    ];

    protected $casts = [
        'is_occupied' => 'boolean',
        'is_operational' => 'boolean',
        'occupied_at' => 'datetime',
        'occupation_expires_at' => 'datetime'
    ];

    public function washroom()
    {
        return $this->belongsTo(Washroom::class);
    }

    public function occupant()
    {
        return $this->belongsTo(User::class, 'occupied_by');
    }

    public function usageLogs()
    {
        return $this->hasMany(ToiletUsageLog::class);
    }

    public function checkAndUpdateExpiry()
    {
        if ($this->is_occupied && $this->occupation_expires_at < Carbon::now()) {
            $this->release();
        }
    }

    public function release()
    {
        if ($this->is_occupied) {
            // Update usage log
            ToiletUsageLog::where('toilet_id', $this->id)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => Carbon::now(),
                    'duration_minutes' => Carbon::now()->diffInMinutes($this->occupied_at)
                ]);

            // Release toilet
            $this->update([
                'is_occupied' => false,
                'occupied_by' => null,
                'occupied_at' => null,
                'occupation_expires_at' => null
            ]);
        }
    }
}
