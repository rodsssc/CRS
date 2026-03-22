<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    /** @use HasFactory<\Database\Factories\MaintenanceFactory> */
    use HasFactory;

    protected $fillable = [
        'car_id',
        'created_by',
        'title',
        'description',
        'service_date',
        'cost',
        'status',
    ];

    protected $casts = [
        'service_date' => 'date',
        'cost' => 'decimal:2',
    ];

    /**
     * Get the car associated with the maintenance record.
     */
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the user who created the maintenance record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
