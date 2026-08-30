<?php

namespace App\Livewire\Portal;

use App\Models\Book;
use App\Services\ReservationService;
use Livewire\Component;

class BookDetail extends Component
{
    public Book $book;

    public function mount(int $id): void
    {
        $this->book = Book::with(['category', 'copies', 'reservations'])->findOrFail($id);
    }

    public function reserve(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('portal.login'), navigate: true);

            return;
        }

        $student = auth()->user()->student;
        if (! $student) {
            session()->flash('error', 'Akun Anda tidak terhubung dengan data siswa.');

            return;
        }

        try {
            app(ReservationService::class)->create($student, $this->book);
            session()->flash('success', 'Reservasi berhasil dibuat.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.portal.book-detail')->layout('components.portal.layout', ['title' => $this->book->title]);
    }
}
