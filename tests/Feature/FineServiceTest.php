<?php

namespace Tests\Feature;

use App\Enums\FineStatus;
use App\Enums\LoanItemStatus;
use App\Enums\LoanStatus;
use App\Services\FineService;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class FineServiceTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBaseTables();
        $this->seedSettings();
    }

    protected function createLateLoan(): array
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $loanService = app(LoanService::class);
        $loan = $loanService->request($student, [$copy->id]);
        $loanService->approve($loan, $staff->id);

        $loan->update([
            'status' => LoanStatus::BORROWED,
            'borrowed_at' => now()->subDays(10),
            'due_at' => now()->subDays(3),
        ]);

        $loan->items()->first()->update([
            'status' => LoanItemStatus::BORROWED,
            'due_at' => now()->subDays(3),
        ]);

        $loan->update([
            'status' => LoanStatus::RETURNED,
            'returned_at' => now(),
        ]);

        return compact('loan', 'copy', 'student', 'staff', 'book');
    }

    public function test_calculate_fine_for_late_return(): void
    {
        ['loan' => $loan, 'student' => $student] = $this->createLateLoan();

        $fineService = app(FineService::class);
        $fine = $fineService->calculateForLoan($loan);

        $this->assertNotNull($fine);
        $this->assertEquals($student->id, $fine->murid_id);
        $this->assertEquals($loan->id, $fine->loan_id);
        $this->assertEquals(FineStatus::UNPAID, $fine->status);
        $this->assertGreaterThan(0, $fine->amount);
        $this->assertGreaterThan(0, $fine->late_days);
    }

    public function test_no_fine_for_on_time_return(): void
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $category = $this->createCategory();
        $book = $this->createBook($category);
        $copy = $this->createBookCopy($book);

        $loanService = app(LoanService::class);
        $loan = $loanService->request($student, [$copy->id]);
        $loanService->approve($loan, $staff->id);

        $loan->update([
            'status' => LoanStatus::BORROWED,
            'borrowed_at' => now()->subDays(3),
            'due_at' => now()->addDays(4),
        ]);

        $loan->items()->first()->update([
            'status' => LoanItemStatus::BORROWED,
            'due_at' => now()->addDays(4),
        ]);

        $loan->update([
            'status' => LoanStatus::RETURNED,
            'returned_at' => now(),
        ]);

        $fineService = app(FineService::class);
        $fine = $fineService->calculateForLoan($loan);

        $this->assertNull($fine);
    }

    public function test_pay_a_fine(): void
    {
        ['loan' => $loan, 'student' => $student] = $this->createLateLoan();

        $fineService = app(FineService::class);
        $fine = $fineService->calculateForLoan($loan);

        $staff = $this->createStaff();
        $fineService->pay($fine, $staff->id, 'Dibayar tunai');

        $fine->refresh();
        $this->assertEquals(FineStatus::PAID, $fine->status);
        $this->assertNotNull($fine->paid_at);
        $this->assertEquals($staff->id, $fine->paid_by);
        $this->assertEquals('Dibayar tunai', $fine->notes);
    }

    public function test_waive_a_fine(): void
    {
        ['loan' => $loan, 'student' => $student] = $this->createLateLoan();

        $fineService = app(FineService::class);
        $fine = $fineService->calculateForLoan($loan);

        $staff = $this->createStaff();
        $fineService->waive($fine, $staff->id, 'Diberikan keringanan');

        $fine->refresh();
        $this->assertEquals(FineStatus::WAIVED, $fine->status);
        $this->assertEquals($staff->id, $fine->paid_by);
        $this->assertEquals('Diberikan keringanan', $fine->notes);
    }

    public function test_get_unpaid_total(): void
    {
        ['loan' => $loan, 'student' => $student] = $this->createLateLoan();

        $fineService = app(FineService::class);
        $fine = $fineService->calculateForLoan($loan);

        $total = $fineService->getUnpaidTotal($student->id);

        $this->assertEquals($fine->amount, $total);
    }

    public function test_get_unpaid_total_returns_zero_when_no_fines(): void
    {
        $student = $this->createStudent();

        $fineService = app(FineService::class);
        $total = $fineService->getUnpaidTotal($student->id);

        $this->assertEquals(0, $total);
    }

    public function test_has_unpaid_fines_returns_true_when_unpaid_exists(): void
    {
        ['loan' => $loan, 'student' => $student] = $this->createLateLoan();

        $fineService = app(FineService::class);
        $fineService->calculateForLoan($loan);

        $this->assertTrue($fineService->hasUnpaidFines($student->id));
    }

    public function test_has_unpaid_fines_returns_false_when_no_fines(): void
    {
        $student = $this->createStudent();

        $fineService = app(FineService::class);
        $this->assertFalse($fineService->hasUnpaidFines($student->id));
    }
}
