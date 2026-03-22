<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'processed_by',
        'payment_type',
        'payment_method',
        'amount',
        'status',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function rental(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function processedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Platform commission for this payment (default 20%).
     */
    public function getCommissionAttribute(): float
    {
        $rate = (float) config('app.platform_commission_rate', 0.20);
        return round((float) $this->amount * $rate, 2);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Check whether the rental this payment belongs to is now fully paid.
     * "Fully paid" = total of all COMPLETED payments >= rental's final_amount.
     *
     * NOTE: This does NOT change any booking status — that is intentionally
     * left to the admin via the "Car Returned" action in BookingController.
     */
    public function rentalIsFullyPaid(): bool
    {
        $rental = $this->rental;

        if (! $rental || (float) $rental->final_amount <= 0) {
            return false;
        }

        $totalPaid = (float) Payment::where('rental_id', $this->rental_id)
            ->where('status', 'completed')
            ->sum('amount');

        return $totalPaid >= (float) $rental->final_amount;
    }
}