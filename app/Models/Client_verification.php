<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}