<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function clientProfile()
    {
        return $this->hasOne(Client_profile::class, 'client_id');
    }

    
    public function client_verifications()
    {
        return $this->hasMany(Client_verification::class, 'client_id');
    }


    public function verifiedVerifications()
    {
        return $this->hasMany(Client_verification::class, 'verified_by');
    }

    public function latestVerification()
{
    return $this->hasOne(Client_verification::class, 'client_id')->latestOfMany();
}

public function hasVerifiedIdentity()
{
    return $this->client_verifications()
        ->where('status', 'approved')
        ->exists();
}

public function hasPendingVerification()
{
    return $this->client_verifications()
        ->where('status', 'pending')
        ->exists();
}


    // Get verification status
    public function getVerificationStatus()
    {
        $latest = $this->latestVerification;
        return $latest ? $latest->status : 'none';
    }

    // Check if user is admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Check if user is client
    public function isClient()
    {
        return $this->role === 'client';
    }

    // Check if user is staff
    public function isStaff()
    {
        return $this->role === 'staff';
    }

    // Check if user is owner
    public function isOwner()
    {
        return $this->role === 'owner';
    }

    // Check if user can verify (admin, staff, or owner)
    public function canVerify()
    {
        return in_array($this->role, ['admin', 'staff']);
    }

    // Check if user can manage all verifications (admin or owner)
    public function canManageAllVerifications()
    {
        return in_array($this->role, ['admin', 'owner']);
    }

    public function Car(){
        return $this->hasMany(Car::class,'owner_id');
    }
}