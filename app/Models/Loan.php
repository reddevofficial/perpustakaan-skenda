<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    protected $fillable = [
        'loan_number', 'murid_id', 'status', 'requested_at', 'approved_at',
        'borrowed_at', 'due_at', 'returned_at', 'approved_by', 'rejected_by',
        'rejection_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'murid_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(LoanExtension::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast() && ! $this->returned_at;
    }

    public static function generateLoanNumber(): string
    {
        return DB::transaction(function () {
            $date = now()->format('Ymd');
            $prefix = "PJ-{$date}-";

            $lastLoan = static::where('loan_number', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('loan_number')
                ->first();

            if ($lastLoan) {
                $lastNumber = (int) substr($lastLoan->loan_number, -4);
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '0001';
            }

            return "{$prefix}{$nextNumber}";
        });
    }
}
