<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    protected $table = 'murid';

    protected $fillable = [
        'nipd', 'nama', 'tanggal_lahir', 'jenis_kelamin', 'alamat',
        'telepon', 'email', 'rombel_id', 'tingkat_id', 'jurusan_id',
        'indeks_id', 'status',
    ];

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'student_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'murid_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'murid_id');
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class, 'murid_id');
    }
}
