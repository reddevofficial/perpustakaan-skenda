<?php

namespace Tests\Feature;

use App\Enums\BookCopyStatus;
use App\Enums\LoanItemStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class LoanServiceTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBaseTables();
        $this->seedSettings();
    }

    public function test_student_can_request_a_loan_with_available_copies(): void
    {
        $roles = $this->createRoles();
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $service = app(LoanService::class);
        $loan = $service->request($student, [$copy->id]);

        $this->assertNotNull($loan);
        $this->assertEquals($student->id, $loan->murid_id);
        $this->assertEquals(LoanStatus::PENDING, $loan->status);
        $this->assertDatabaseHas('loan_items', [
            'loan_id' => $loan->id,
            'book_copy_id' => $copy->id,
            'status' => LoanItemStatus::PENDING,
        ]);
    }

    public function test_loan_starts_as_pending(): void
    {
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $service = app(LoanService::class);
        $loan = $service->request($student, [$copy->id]);

        $this->assertEquals(LoanStatus::PENDING, $loan->status);
        $this->assertNull($loan->approved_at);
        $this->assertNull($loan->borrowed_at);
    }

    public function test_staff_can_approve_a_loan(): void
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $loanService = app(LoanService::class);
        $loan = $loanService->request($student, [$copy->id]);

        $loanService->approve($loan, $staff->id);

        $loan->refresh();
        $this->assertEquals(LoanStatus::APPROVED, $loan->status);
        $this->assertNotNull($loan->approved_at);
        $this->assertEquals($staff->id, $loan->approved_by);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'loan_approved',
            'model_type' => Loan::class,
            'model_id' => $loan->id,
        ]);
    }

    public function test_staff_can_reject_a_loan_with_reason(): void
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $loanService = app(LoanService::class);
        $loan = $loanService->request($student, [$copy->id]);

        $reason = 'Buku sedang dalam perbaikan';
        $loanService->reject($loan, $staff->id, $reason);

        $loan->refresh();
        $this->assertEquals(LoanStatus::REJECTED, $loan->status);
        $this->assertEquals($staff->id, $loan->rejected_by);
        $this->assertEquals($reason, $loan->rejection_reason);
    }

    public function test_mark_borrowed_changes_status_to_borrowed_and_updates_book_copies(): void
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

        $loan->refresh();
        $copy->refresh();

        $this->assertEquals(LoanStatus::BORROWED, $loan->status);
        $this->assertNotNull($loan->borrowed_at);
        $this->assertNotNull($loan->due_at);
        $this->assertEquals(BookCopyStatus::BORROWED, $copy->status);

        $loanItem = $loan->items()->first();
        $this->assertEquals(LoanItemStatus::BORROWED, $loanItem->status);
        $this->assertNotNull($loanItem->due_at);
    }

    public function test_cannot_request_loan_with_unavailable_copies(): void
    {
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book, BookCopyStatus::BORROWED);

        $loanService = app(LoanService::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Buku {$copy->barcode} tidak tersedia.");

        $loanService->request($student, [$copy->id]);
    }

    public function test_cancel_a_pending_loan(): void
    {
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $loanService = app(LoanService::class);
        $loan = $loanService->request($student, [$copy->id]);

        $loanService->cancel($loan);

        $loan->refresh();
        $this->assertEquals(LoanStatus::CANCELLED, $loan->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'loan_cancelled',
            'model_id' => $loan->id,
        ]);
    }
}
