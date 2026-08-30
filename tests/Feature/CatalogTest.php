<?php

namespace Tests\Feature;

use App\Enums\BookCopyStatus;
use App\Livewire\Portal\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class CatalogTest extends TestCase
{
    use CreatesTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBaseTables();
        $this->seedSettings();
    }

    protected function seedCatalogData(): array
    {
        $category1 = $this->createCategory(['name' => 'Fiksi', 'slug' => 'fiksi']);
        $category2 = $this->createCategory(['name' => 'Sains', 'slug' => 'sains']);

        $book1 = $this->createBook($category1, ['title' => 'Laskar Pelangi', 'author' => 'Andrea Hirata']);
        $book2 = $this->createBook($category1, ['title' => 'Bumi', 'author' => 'Tere Liye']);
        $book3 = $this->createBook($category2, ['title' => 'Sapiens', 'author' => 'Yuval Harari']);

        $this->createBookCopy($book1, BookCopyStatus::AVAILABLE);
        $this->createBookCopy($book1, BookCopyStatus::AVAILABLE);
        $this->createBookCopy($book2, BookCopyStatus::BORROWED);
        $this->createBookCopy($book3, BookCopyStatus::AVAILABLE);

        return compact('category1', 'category2', 'book1', 'book2', 'book3');
    }

    public function test_guest_can_view_catalog(): void
    {
        $this->seedCatalogData();

        $response = $this->get(route('portal.catalog'));
        $response->assertStatus(200);
    }

    public function test_search_filters_books_by_title(): void
    {
        $this->seedCatalogData();

        Livewire::test(Catalog::class)
            ->set('search', 'Laskar')
            ->assertSee('Laskar Pelangi')
            ->assertDontSee('Sapiens');
    }

    public function test_search_filters_books_by_author(): void
    {
        $this->seedCatalogData();

        Livewire::test(Catalog::class)
            ->set('search', 'Tere Liye')
            ->assertSee('Bumi')
            ->assertDontSee('Laskar Pelangi');
    }

    public function test_category_filter_works(): void
    {
        $data = $this->seedCatalogData();

        Livewire::test(Catalog::class)
            ->set('categoryId', $data['category1']->id)
            ->assertSee('Laskar Pelangi')
            ->assertSee('Bumi')
            ->assertDontSee('Sapiens');
    }

    public function test_availability_filter_shows_available_books(): void
    {
        $data = $this->seedCatalogData();

        Livewire::test(Catalog::class)
            ->set('availability', 'available')
            ->assertSee('Laskar Pelangi')
            ->assertSee('Sapiens')
            ->assertDontSee('Bumi');
    }

    public function test_availability_filter_shows_unavailable_books(): void
    {
        $data = $this->seedCatalogData();

        Livewire::test(Catalog::class)
            ->set('availability', 'unavailable')
            ->assertSee('Bumi')
            ->assertDontSee('Laskar Pelangi')
            ->assertDontSee('Sapiens');
    }

    public function test_combined_search_and_category_filter(): void
    {
        $data = $this->seedCatalogData();

        Livewire::test(Catalog::class)
            ->set('search', 'Bumi')
            ->set('categoryId', $data['category1']->id)
            ->assertSee('Bumi')
            ->assertDontSee('Laskar Pelangi')
            ->assertDontSee('Sapiens');
    }
}
