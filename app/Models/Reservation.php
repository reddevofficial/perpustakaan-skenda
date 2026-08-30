<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_number', 'murid_id', 'book_id', 'status', 'reserved_at',
        'expires_at', 'queue_position', 'fulfilled_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'reserved_at' => 'datetime',
            'expires_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'murid_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public static function generateReservationNumber(): string
    {
        return DB::transaction(function () {
            $date = now()->format('Ymd');
            $prefix = "RSV-{$date}-";

            $lastReservation = static::where('reservation_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('reservation_number')
                ->first();

            if ($lastReservation) {
                $lastNumber = (int) substr($lastReservation->reservation_number, -4);
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '0001';
            }

            return "{$prefix}{$nextNumber}";
        });
    }
}
