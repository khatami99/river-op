<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class StafDinas extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'staf_dinas';

    protected $fillable = [
        'nama',
        'username',
        'password',
        'role',
        'aktif',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'aktif' => 'boolean',
    ];
}
