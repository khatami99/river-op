<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Staf dinas hanya mengakses dashboard web lewat session auth.
 * Sengaja tidak memakai HasApiTokens supaya token Sanctum miliknya
 * tidak bisa dipakai menembak endpoint API milik juru sungai.
 */
class StafDinas extends Authenticatable
{
    use SoftDeletes;

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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
