<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Client_verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'id_type',
        'id_number',
        'id_front_image_path',
        'id_back_image_path',
        'selfie_with_id_image_path',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Approve verification
    public function approve($adminId = null)
    {
        $this->update([
            'status' => 'approved',
            'verified_at' => now(),
            'verified_by' => $adminId ?? Auth::id(),
            'rejection_reason' => null, // Clear any previous rejection reason
        ]);

        // Optional: Fire event or notification
        // event(new VerificationApproved($this));
        
        return $this;
    }

    // Reject verification
    public function reject($reason, $adminId = null)
    {
        $this->update([
            'status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => $adminId ?? Auth::id(),
            'rejection_reason' => $reason,
        ]);

        // Optional: Fire event or notification
        // event(new VerificationRejected($this));
        
        return $this;
    }

    // Set to pending status
    public function setPending()
    {
        $this->update([
            'status' => 'pending',
            'verified_at' => null,
            'verified_by' => null,
            'rejection_reason' => null,
        ]);
        
        return $this;
    }

    // Check if verification is approved
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    // Check if verification is pending
    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Check if verification is rejected
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Scope for filtering by status
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

    // Get formatted ID type
    public function getFormattedIdTypeAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->id_type));
    }

    // Get status badge color
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}