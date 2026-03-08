<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    /** @use HasFactory<\Database\Factories\RentalFactory> */
    use HasFactory;
    protected $fillable =[
        'client_id',
        'car_id',
        'destinationFrom',
        'destinationTo',
        'rental_start_date',
        'rental_end_date',
        'total_days',
        'total_hours',
        
        'car_amount',
        'destination_amount',
        'discount_amount',
        'final_amount',
        'status',
    ];

    protected $casts = [
    'rental_start_date' => 'datetime',
    'rental_end_date' => 'datetime',
];

    public function client()
    {
        return $this->belongsTo(User::class,'client_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class,'car_id');
    }

   

    public function scopeOngoing($query)
{
    return $query->where('status', 'ongoing');
}

public function scopeCompleted($query)
{
    return $query->where('status', 'completed');
}

public function scopePending($query)
{
    return $query->where('status', 'pending');
}

public function scopeCancelled($query)
{
    return $query->where('status', 'cancelled');
}


}
