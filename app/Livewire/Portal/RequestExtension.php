<?php

namespace App\Livewire\Portal;

use App\Models\Loan;
use App\Services\LoanService;
use Livewire\Component;

class RequestExtension extends Component
{
    public $loanId;

    public $loan;

    public $reason = '';

    protected $rules = [
        'reason' => 'required|string|min:3|max:500',
    ];

    protected $messages = [
        'reason.required' => 'Alasan perpanjangan wajib diisi.',
        'reason.min' => 'Alasan minimal 3 karakter.',
        'reason.max' => 'Alasan maksimal 500 karakter.',
    ];

    public function mount(int $loan_id): void
    {
        $student = auth()->user()->student;

        $this->loanId = $loan_id;
        $this->loan = Loan::with(['items.bookCopy.book'])
            ->where('id', $loan_id)
            ->where('murid_id', $student?->id)
            ->firstOrFail();
    }

    public function submit(): void
    {
        $this->validate();

        try {
            if ($this->loan->status->value !== 'borrowed') {
                session()->flash('error', 'Hanya peminjaman dengan status dipinjam yang bisa diperpanjang.');

                return;
            }

            $loanService = app(LoanService::class);

            if (! $loanService->canExtend($this->loan)) {
                session()->flash('error', 'Peminjaman ini tidak bisa diperpanjang. Kemungkinan sudah mencapai batas maksimal perpanjangan atau ada reservasi buku.');

                return;
            }

            $loanService->requestExtension(
                $this->loan,
                auth()->user(),
                $this->reason
            );

            session()->flash('success', 'Permintaan perpanjangan berhasil diajukan. Menunggu persetujuan petugas.');

            $this->redirect(route('portal.loans'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.portal.request-extension')
            ->layout('components.portal.layout', ['title' => 'Ajukan Perpanjangan']);
    }
}
