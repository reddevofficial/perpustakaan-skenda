<?php

namespace App\Models;

use App\Enums\BookCopyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['book_id', 'barcode', 'condition', 'status', 'shelf_location', 'notes'];

    protected function casts(): array
    {
        return ['status' => BookCopyStatus::class];
    }

    protected static function booted(): void
    {
        static::created(function (BookCopy $copy): void {
            $copy->book()->increment('stock');
            if ($copy->status === BookCopyStatus::AVAILABLE) {
                $copy->book()->increment('available_stock');
            }
        });

        static::deleted(function (BookCopy $copy): void {
            $copy->book()->decrement('stock');
            if ($copy->status === BookCopyStatus::AVAILABLE) {
                $copy->book()->decrement('available_stock');
            }
        });
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }
}
