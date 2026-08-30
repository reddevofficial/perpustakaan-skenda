<?php

namespace App\Services;

use App\Enums\BookCopyStatus;
use App\Models\Book;

class BookStockService
{
    public function getAvailableCount(Book $book): int
    {
        return $book->copies()
            ->where('status', BookCopyStatus::AVAILABLE)
            ->count();
    }

    public function getTotalCount(Book $book): int
    {
        return $book->copies()->count();
    }

    public function getBorrowedCount(Book $book): int
    {
        return $book->copies()
            ->where('status', BookCopyStatus::BORROWED)
            ->count();
    }

    public function isAvailable(Book $book): bool
    {
        return $this->getAvailableCount($book) > 0;
    }
}
