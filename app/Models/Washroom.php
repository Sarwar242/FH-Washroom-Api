<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Washroom extends Model
{
    protected $fillable = ['name', 'floor', 'type', 'is_operational'];

    protected $casts = [
        'is_operational' => 'boolean'
    ];

    public function toilets()
    {
        return $this->hasMany(Toilet::class);
    }

    public function getAvailableToiletsCount()
    {
        return $this->toilets()
            ->where('is_operational', true)
            ->where('is_occupied', false)
            ->count();
    }
}
