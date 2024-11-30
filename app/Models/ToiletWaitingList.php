<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToiletWaitingList extends Model
{
    protected $fillable = [
        'toilet_id',
        'user_id',
        'joined_at',
        'notified_at',
        'expires_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'notified_at' => 'datetime',
        'expires_at' => 'datetime'
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
