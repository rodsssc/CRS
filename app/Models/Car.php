<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    /** @use HasFactory<\Database\Factories\CarFactory> */
    use HasFactory;
    protected $fillable = [
        'owner_id',
        'plate_number',
        'brand',
        'model',
        'year',
        'color',
        'capacity',
        'transmission_type',
        'fuel_type',
        'rental_price_per_day',
        'image_path',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'rental_price_per_day' => 'decimal:2',
    ];

    public function Owner(){
        return $this->belongsTo(User::class,'owner_id');
    }
}
