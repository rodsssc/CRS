<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car_gps extends Model
{
    /** @use HasFactory<\Database\Factories\CarGpsFactory> */
    use HasFactory;
    protected $fillable = [
        'car_id',
        'latitude',
        'longitude',
        'recorded_at',
    ];
}
