<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Client_verification extends Model
{
    use HasFactory;

    
    protected $table = 'client_verifications';

    protected $fillable = [
        'client_id',
        'id_type',
        'id_number',
        'id_front_image_path',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at'  => 'datetime',
    ];

    // =========================================================
    // RELATIONSHIPS
    // =========================================================
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // verified_by stores an admin name string, NOT a foreign key —
    // so no belongsTo here; remove verifier() if verified_by is a string.
    // If it IS a foreign key to users.id, keep this:
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // =========================================================
    // ACTIONS
    // =========================================================
    public function approve($adminId = null)
    {
        $this->update([
            'status'           => 'approved',
            'verified_at'      => now(),
            'verified_by'      => $adminId ?? Auth::id(),
            'rejection_reason' => null,
        ]);

        return $this;
    }

    public function reject($reason = null, $adminId = null)
    {
        $this->update([
            'status'           => 'rejected',
            'verified_at'      => now(),
            'verified_by'      => $adminId ?? Auth::id(),
            'rejection_reason' => $reason,
        ]);

        return $this;
    }

    public function setPending()
    {
        $this->update([
            'status'           => 'pending',
            'verified_at'      => null,
            'verified_by'      => null,
            'rejection_reason' => null,
        ]);

        return $this;
    }

    // =========================================================
    // HELPERS
    // =========================================================
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // =========================================================
    // SCOPES
    // =========================================================
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // =========================================================
    // ACCESSORS
    // =========================================================
    public function getFormattedIdTypeAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->id_type ?? ''));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'pending'  => 'warning',
            'rejected' => 'danger',
            default    => 'secondary',
        };
    }
}