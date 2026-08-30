<?php

namespace App\Models;

use App\Enums\LoanItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanItem extends Model
{
    protected $fillable = ['loan_id', 'book_copy_id', 'due_at', 'returned_at', 'status'];

    protected function casts(): array
    {
        return [
            'status' => LoanItemStatus::class,
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }
}
