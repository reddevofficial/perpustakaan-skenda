<?php

namespace Tests\Feature;

use App\Enums\BookCopyStatus;
use App\Enums\LoanItemStatus;
use App\Enums\LoanStatus;
use App\Services\LoanService;
use App\Services\ReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ReturnServiceTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBaseTables();
        $this->seedSettings();
    }

    protected function createBorrowedLoan(): array
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $loanService = app(LoanService::class);
        $loan = $loanService->request($student, [$copy->id]);
        $loanService->approve($loan, $staff->id);
        $loanService->markBorrowed($loan);

        return compact('loan', 'copy', 'student', 'staff', 'book');
    }

    public function test_return_a_single_item_changes_status_to_returned(): void
    {
        ['loan' => $loan, 'copy' => $copy] = $this->createBorrowedLoan();
        $loanItem = $loan->items()->first();

        $returnService = app(ReturnService::class);
        $returnService->returnItem($loanItem);

        $loanItem->refresh();
        $loan->refresh();

        $this->assertEquals(LoanItemStatus::RETURNED, $loanItem->status);
        $this->assertNotNull($loanItem->returned_at);
        $this->assertEquals(LoanStatus::RETURNED, $loan->status);
        $this->assertNotNull($loan->returned_at);
    }

    public function test_return_makes_book_copy_available_again(): void
    {
        ['loan' => $loan, 'copy' => $copy] = $this->createBorrowedLoan();
        $loanItem = $loan->items()->first();

        $copy->refresh();
        $this->assertEquals(BookCopyStatus::BORROWED, $copy->status);

        $returnService = app(ReturnService::class);
        $returnService->returnItem($loanItem);

        $copy->refresh();
        $this->assertEquals(BookCopyStatus::AVAILABLE, $copy->status);
    }

    public function test_double_return_throws_exception(): void
    {
        ['loan' => $loan] = $this->createBorrowedLoan();
        $loanItem = $loan->items()->first();

        $returnService = app(ReturnService::class);
        $returnService->returnItem($loanItem);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Buku sudah dikembalikan sebelumnya.');

        $returnService->returnItem($loanItem);
    }

    public function test_process_return_handles_multiple_items(): void
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy1 = $this->createBookCopy($book);
        $copy2 = $this->createBookCopy($book);

        $loanService = app(LoanService::class);
        $loan = $loanService->request($student, [$copy1->id, $copy2->id]);
        $loanService->approve($loan, $staff->id);
        $loanService->markBorrowed($loan);

        $this->assertEquals(LoanStatus::BORROWED, $loan->status);
        $this->assertEquals(2, $loan->items()->where('status', LoanItemStatus::BORROWED)->count());

        $returnService = app(ReturnService::class);
        $returnService->processReturn($loan);

        $loan->refresh();
        $copy1->refresh();
        $copy2->refresh();

        $this->assertEquals(LoanStatus::RETURNED, $loan->status);
        $this->assertEquals(BookCopyStatus::AVAILABLE, $copy1->status);
        $this->assertEquals(BookCopyStatus::AVAILABLE, $copy2->status);
        $this->assertEquals(2, $loan->items()->where('status', LoanItemStatus::RETURNED)->count());
    }
}
