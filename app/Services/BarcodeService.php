<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCopy;

class BarcodeService
{
    public function generateForBook(Book $book): string
    {
        $lastCopy = BookCopy::where('book_id', $book->id)
            ->orderByDesc('barcode')
            ->first();

        if ($lastCopy && preg_match('/BK-(\d{6})/', $lastCopy->barcode, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $baseNumber = ($book->id * 100);
            $existingCount = BookCopy::where('book_id', $book->id)->count();
            $nextNumber = $baseNumber + $existingCount + 1;
        }

        return 'BK-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function lookupByBarcode(string $barcode): ?BookCopy
    {
        return BookCopy::with('book.category')->where('barcode', $barcode)->first();
    }
}
