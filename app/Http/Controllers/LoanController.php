<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Services\LoanService;
use App\Services\ReturnBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;

class LoanController extends Controller
{
    public function store(Book $book, LoanService $loanService): RedirectResponse
    {
        $student = Auth::user()?->student;
        abort_unless($student, 403);

        try {
            $loanService->request($student, $book);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Pengajuan peminjaman berhasil dikirim.');
    }

    public function approve(Loan $loan, LoanService $loanService): RedirectResponse
    {
        Gate::authorize('manage-loans');
        $loanService->approve($loan, Auth::id());

        return back()->with('success', 'Peminjaman disetujui.');
    }

    public function reject(Loan $loan, LoanService $loanService): RedirectResponse
    {
        Gate::authorize('manage-loans');
        $loanService->reject($loan, Auth::id(), request('reason'));

        return back()->with('success', 'Peminjaman ditolak.');
    }

    public function handover(Loan $loan, LoanService $loanService): RedirectResponse
    {
        Gate::authorize('manage-loans');
        $loanService->markBorrowed($loan);

        return back()->with('success', 'Buku ditandai sudah dipinjam.');
    }

    public function return(Loan $loan, ReturnBookService $returnBookService): RedirectResponse
    {
        Gate::authorize('manage-loans');
        $returnBookService->return(
            $loan,
            app(\App\Services\FineService::class),
            app(\App\Services\ReservationService::class),
            request('notes'),
        );

        return back()->with('success', 'Pengembalian berhasil diproses.');
    }

    public function mine(): View
    {
        $student = Auth::user()?->student;
        abort_unless($student, 403);

        return view('loans.mine', [
            'loans' => $student->loans()->with('items.bookCopy.book', 'fine')->latest()->paginate(15),
        ]);
    }
}
