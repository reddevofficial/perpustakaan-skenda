<?php

namespace App\Livewire\Portal;

use App\Models\Book;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class Catalog extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryId = null;

    public string $availability = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Book::with(['category', 'copies'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('author', 'like', "%{$this->search}%")
                ->orWhere('isbn', 'like', "%{$this->search}%")
                ->orWhere('publisher', 'like', "%{$this->search}%")
            ))
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->availability === 'available', fn ($q) => $q->whereHas('copies', fn ($c) => $c->where('status', 'available')))
            ->when($this->availability === 'unavailable', fn ($q) => $q->whereDoesntHave('copies', fn ($c) => $c->where('status', 'available')));

        $books = $query->paginate(12);
        $categories = Category::withCount('books')->orderBy('name')->get();

        return view('livewire.portal.catalog', [
            'books' => $books,
            'categories' => $categories,
        ])->layout('components.portal.layout', ['title' => 'Katalog Buku']);
    }
}
