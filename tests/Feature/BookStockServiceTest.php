<?php

namespace Tests\Feature;

use App\Enums\BookCopyStatus;
use App\Services\BookStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class BookStockServiceTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBaseTables();
    }

    public function test_get_available_count(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $this->createBookCopy($book, BookCopyStatus::AVAILABLE);
        $this->createBookCopy($book, BookCopyStatus::AVAILABLE);
        $this->createBookCopy($book, BookCopyStatus::BORROWED);

        $service = app(BookStockService::class);
        $available = $service->getAvailableCount($book);

        $this->assertEquals(2, $available);
    }

    public function test_get_available_count_returns_zero_when_none_available(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $this->createBookCopy($book, BookCopyStatus::BORROWED);
        $this->createBookCopy($book, BookCopyStatus::DAMAGED);

        $service = app(BookStockService::class);
        $available = $service->getAvailableCount($book);

        $this->assertEquals(0, $available);
    }

    public function test_get_total_count(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $this->createBookCopy($book, BookCopyStatus::AVAILABLE);
        $this->createBookCopy($book, BookCopyStatus::BORROWED);
        $this->createBookCopy($book, BookCopyStatus::DAMAGED);

        $service = app(BookStockService::class);
        $total = $service->getTotalCount($book);

        $this->assertEquals(3, $total);
    }

    public function test_get_total_count_returns_zero_when_no_copies(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $service = app(BookStockService::class);
        $total = $service->getTotalCount($book);

        $this->assertEquals(0, $total);
    }

    public function test_is_available_returns_true_when_copies_are_available(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $this->createBookCopy($book, BookCopyStatus::AVAILABLE);

        $service = app(BookStockService::class);
        $this->assertTrue($service->isAvailable($book));
    }

    public function test_is_available_returns_false_when_no_copies_available(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $this->createBookCopy($book, BookCopyStatus::BORROWED);

        $service = app(BookStockService::class);
        $this->assertFalse($service->isAvailable($book));
    }

    public function test_is_available_returns_false_when_no_copies_exist(): void
    {
        $category = $this->createCategory();
        $book = $this->createBook($category);

        $service = app(BookStockService::class);
        $this->assertFalse($service->isAvailable($book));
    }
}
