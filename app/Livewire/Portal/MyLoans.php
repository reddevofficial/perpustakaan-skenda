<?php

namespace App\Livewire\Portal;

use App\Services\LoanService;
use Livewire\Component;

class MyLoans extends Component
{
    public function render()
    {
        $student = auth()->user()->student;
        $loans = $student?->loans()
            ->with(['items.bookCopy.book', 'extensions'])
            ->latest()
            ->get() ?? collect();

        $loanService = app(LoanService::class);
        $extendableLoanIds = $loans
            ->filter(fn ($loan) => $loanService->canExtend($loan))
            ->pluck('id')
            ->toArray();

        return view('livewire.portal.my-loans', [
            'loans' => $loans,
            'extendableLoanIds' => $extendableLoanIds,
        ])->layout('components.portal.layout', ['title' => 'Pinjaman Saya']);
    }
}
