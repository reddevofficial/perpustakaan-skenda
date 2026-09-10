<?php

namespace App\Livewire\Portal;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class BookDetail extends Component
{
    public Book $book;

    public function mount(Book $book): void
    {
        abort_unless($book->status === 'active', 404);
        $this->book = $book->load(['category', 'copies']);
    }
    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.portal.book-detail');
    }
}
