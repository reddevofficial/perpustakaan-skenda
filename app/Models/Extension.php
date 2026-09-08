<?php

namespace App\Models;

use App\Enums\ExtensionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id', 'student_id', 'approved_by', 'status', 'extension_number',
        'requested_due_at', 'approved_due_at', 'reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExtensionStatus::class,
            'requested_due_at' => 'date',
            'approved_due_at' => 'date',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
