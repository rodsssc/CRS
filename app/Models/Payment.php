<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
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
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * The rental/booking this payment belongs to.
     */
    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    /**
     * Admin/staff user who processed the payment.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Platform commission for this payment.
     *
     * By default this uses a 20% rate, but it can be adjusted via
     * app.platform_commission_rate config if needed.
     */
    public function getCommissionAttribute(): float
    {
        $rate = (float) config('app.platform_commission_rate', 0.20);

        return round((float) $this->amount * $rate, 2);
    }
}
