<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client_profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'date_birth',
        'address',
        'nationality',
        'facebook_name',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'date_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Get full name
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // Get formatted date of birth
    public function getFormattedDateBirthAttribute()
    {
        return $this->date_birth ? $this->date_birth->format('M d, Y') : 'N/A';
    }

    // Check if profile is complete
    public function isComplete()
    {
        return !empty($this->first_name) 
            && !empty($this->last_name) 
            && !empty($this->date_birth)
            && !empty($this->address);
    }
}