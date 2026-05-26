<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kabupaten extends Model
{
    use SoftDeletes;

    protected $table = 'kabupaten';

    protected $fillable = [
        'nama',
        'kode',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function wilayahSungai(): HasMany
    {
        return $this->hasMany(WilayahSungai::class, 'kabupaten_id');
    }
}
