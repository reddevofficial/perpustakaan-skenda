<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'book_code', 'isbn', 'title', 'author', 'publisher',
        'publication_year', 'stock', 'available_stock', 'shelf_location',
        'cover', 'description', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Book $book): void {
            $book->book_code ??= 'BK-'.str_pad((string) ((Book::withTrashed()->max('id') ?? 0) + 1), 6, '0', STR_PAD_LEFT);
            $book->available_stock ??= $book->stock ?? 0;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return $this->available_stock > 0 ? 'Tersedia' : 'Stok habis';
    }
}
