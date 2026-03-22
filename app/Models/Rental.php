<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'returned_at',
    ];

    protected $casts = [
        'rental_start_date' => 'datetime',
        'rental_end_date'   => 'datetime',
        'returned_at'       => 'datetime',
    ];

    // =========================================================================
    // BOOT — automatic side-effects when booking status changes
    //
    //  Rule: When a booking moves to "completed" OR "cancelled",
    //        the linked car is automatically set back to "available".
    //
    //  Rule: Booking status is NEVER changed here based on payment.
    //        Payment completion only informs the admin UI — the admin
    //        must explicitly click "Car Returned" to complete a booking.
    // =========================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::updated(function (Rental $rental) {
            // Only react when the status column actually changed
            if (! $rental->wasChanged('status')) {
                return;
            }

            $newStatus = $rental->status;

            // Release the car when the booking ends (completed or cancelled)
            if (in_array($newStatus, ['completed', 'cancelled'], true)) {
                if ($rental->car_id) {
                    Car::where('id', $rental->car_id)
                        ->update(['status' => 'available']);
                }
            }
        });
    }

    public function isPending(){
        $this->status === 'pending';
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function car(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class, 'rental_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopePending($query)    { return $query->where('status', 'pending'); }
    public function scopeOngoing($query)    { return $query->where('status', 'ongoing'); }
    public function scopeCompleted($query)  { return $query->where('status', 'completed'); }
    public function scopeCancelled($query)  { return $query->where('status', 'cancelled'); }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Total amount paid (completed payments only).
     */
    public function totalPaid(): float
    {
        return (float) $this->payments()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Remaining balance.
     */
    public function remainingBalance(): float
    {
        return max(0, (float) $this->final_amount - $this->totalPaid());
    }

    /**
     * Whether the rental is fully settled.
     */
    public function isFullyPaid(): bool
    {
        return (float) $this->final_amount > 0
            && $this->totalPaid() >= (float) $this->final_amount;
    }
}