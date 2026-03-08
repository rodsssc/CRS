<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification_log extends Model
{
    /** @use HasFactory<\Database\Factories\VerificationLogFactory> */
    use HasFactory;

    protected $fillable = [
        'verification_id',
        'admin_id',
        'action',
        'remarks',
     ];


     public function verification()
    {
        return $this->belongsTo(Client_verification::class, 'verification_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
