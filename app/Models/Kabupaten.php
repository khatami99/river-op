<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function das(): BelongsToMany
    {
        return $this->belongsToMany(Das::class, 'das_kabupaten', 'kabupaten_id', 'das_id')
            ->withTimestamps();
    }
}
