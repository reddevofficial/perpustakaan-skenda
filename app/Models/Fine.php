<?php

namespace App\Models;

use App\Enums\FineStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $fillable = [
        'loan_id', 'murid_id', 'amount', 'late_days', 'status',
        'paid_at', 'paid_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'late_days' => 'integer',
            'status' => FineStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'murid_id');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
