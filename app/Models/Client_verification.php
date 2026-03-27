<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Client_verification extends Model
{
    protected $table = 'client_verifications';
    
    protected $fillable = [
        'client_id',
        'id_type',
        'id_number',
        'id_front_image_path',
        'status',
        'rejection_reason',
        'submitted_at',
        'verified_at',
        'verified_by',
    ];
    
    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
    
    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================
    
    /**
     * Get the client (user) that owns this verification
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    
    /**
     * Get the admin who verified this record
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    
    // =========================================================================
    // SCOPES
    // =========================================================================
    
    /**
     * Scope a query to only include pending verifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only include approved verifications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope a query to only include rejected verifications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
    
    /**
     * Get the count of pending verifications (static method for controller)
     */
    public static function pendingCount(): int
    {
        return self::where('status', 'pending')->count();
    }
    
    /**
     * Get the count of approved verifications
     */
    public static function approvedCount(): int
    {
        return self::where('status', 'approved')->count();
    }
    
    /**
     * Get the count of rejected verifications
     */
    public static function rejectedCount(): int
    {
        return self::where('status', 'rejected')->count();
    }
    
    // =========================================================================
    // ACCESSORS & MUTATORS
    // =========================================================================
    
    /**
     * Get formatted ID type for display
     */
    public function getFormattedIdTypeAttribute(): string
    {
        $types = [
            'passport' => 'Passport',
            'drivers_license' => 'Driver\'s License',
            'national_id' => 'National ID (PhilSys)',
            'voters_id' => 'Voter\'s ID',
            'sss' => 'SSS ID',
            'umid' => 'UMID',
            'philhealth' => 'PhilHealth ID',
            'postal_id' => 'Postal ID',
            'prc_id' => 'PRC ID',
            'tin_id' => 'TIN ID',
        ];
        
        return $types[$this->id_type] ?? ucfirst(str_replace('_', ' ', $this->id_type));
    }
    
    /**
     * Get status with badge class for display
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-success">Approved</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
    
    /**
     * Check if verification is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if verification is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    
    /**
     * Check if verification is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
    
    // =========================================================================
    // BUSINESS LOGIC METHODS
    // =========================================================================
    
    /**
     * Approve the verification
     */
    public function approve(int $adminId): self
    {
        $this->status = 'approved';
        $this->verified_at = now();
        $this->verified_by = $adminId;
        $this->rejection_reason = null;
        $this->save();
        
        Log::info('Verification approved', [
            'verification_id' => $this->id,
            'client_id' => $this->client_id,
            'admin_id' => $adminId,
        ]);
        
        return $this;
    }
    
    /**
     * Reject the verification
     */
    public function reject(string $reason, int $adminId): self
    {
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->verified_at = now();
        $this->verified_by = $adminId;
        $this->save();
        
        Log::info('Verification rejected', [
            'verification_id' => $this->id,
            'client_id' => $this->client_id,
            'admin_id' => $adminId,
            'reason' => $reason,
        ]);
        
        return $this;
    }
    
    /**
     * Mark as pending (for resubmission)
     */
    public function markAsPending(): self
    {
        $this->status = 'pending';
        $this->rejection_reason = null;
        $this->verified_at = null;
        $this->verified_by = null;
        $this->submitted_at = now();
        $this->save();
        
        Log::info('Verification marked as pending', [
            'verification_id' => $this->id,
            'client_id' => $this->client_id,
        ]);
        
        return $this;
    }
    
    // =========================================================================
    // BOOT METHOD (for model events)
    // =========================================================================
    
    protected static function boot()
    {
        parent::boot();
        
        // Auto-set submitted_at when creating a new verification
        static::creating(function ($model) {
            if (empty($model->submitted_at)) {
                $model->submitted_at = now();
            }
            if (empty($model->status)) {
                $model->status = 'pending';
            }
        });
    }
}