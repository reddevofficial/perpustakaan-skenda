<?php

namespace App\Livewire\Portal;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Catalog extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    public function render(): View
    {
        $books = Book::query()
            ->with('category')
            ->where('status', 'active')
            ->when($this->search !== '', function ($query): void {
                $search = '%'.$this->search.'%';
                $query->where(function ($bookQuery) use ($search): void {
                    $bookQuery->where('title', 'like', $search)
                        ->orWhere('author', 'like', $search)
                        ->orWhere('isbn', 'like', $search)
                        ->orWhere('book_code', 'like', $search)
                        ->orWhereHas('copies', fn ($copyQuery) => $copyQuery->where('barcode', 'like', $search));
                });
            })
            ->when($this->categoryId !== '', fn ($query) => $query->where('category_id', $this->categoryId))
            ->when($this->year !== '', fn ($query) => $query->where('publication_year', $this->year))
            ->when($this->availability === 'available', fn ($query) => $query->where('available_stock', '>', 0))
            ->when($this->availability === 'unavailable', fn ($query) => $query->where('available_stock', 0))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(12);

        return view('livewire.portal.catalog', [
            'books' => $books,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'years' => Book::query()->whereNotNull('publication_year')->distinct()->orderByDesc('publication_year')->pluck('publication_year'),
        ]);
    }

    public string $search = '';
    public string $categoryId = '';
    public string $availability = '';
    public string $year = '';
    public string $sortBy = 'title';
    public string $sortDirection = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedAvailability(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPage();
    }

    public function toggleSortDirection(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryId', 'availability', 'year']);
        $this->resetPage();
    }

}
