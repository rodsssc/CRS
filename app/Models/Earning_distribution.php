<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning_distribution extends Model
{
    /** @use HasFactory<\Database\Factories\EarningDistributionFactory> */
    use HasFactory;
    protected $fillable = [
        'rental_id',
        'owner_share',
        'admin_id',
        'gross_ammount',
        'commission_rate_id',
        'admin_commission_ammount',
        'owner_net_ammount',
    ];
}
