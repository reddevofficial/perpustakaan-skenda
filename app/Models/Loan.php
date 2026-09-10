<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_code', 'student_id', 'approved_by', 'status', 'requested_at', 'approved_at',
        'borrowed_at', 'due_at', 'returned_at', 'extension_count', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'borrowed_at' => 'datetime',
            'due_at' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(Extension::class);
    }
}
