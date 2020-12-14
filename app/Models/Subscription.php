<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'deviceId',
        'appId',
        'receipt',
        'status',
        'expire_date'
    ];

    use HasFactory;
}
