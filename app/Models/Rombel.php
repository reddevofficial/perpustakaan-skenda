<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $fillable = ['tahun_masuk', 'tingkat_id', 'jurusan_id', 'indeks_id', 'wali_guru_id'];

    public function tingkat(): BelongsTo
    {
        return $this->belongsTo(Tingkat::class);
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function indeks(): BelongsTo
    {
        return $this->belongsTo(Indeks::class);
    }
}
