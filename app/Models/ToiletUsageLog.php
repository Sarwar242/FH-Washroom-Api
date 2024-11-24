<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToiletUsageLog extends Model
{
    protected $fillable = [
        'toilet_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_minutes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function toilet()
    {
        return $this->belongsTo(Toilet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
