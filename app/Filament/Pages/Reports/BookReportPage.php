<?php

namespace App\Filament\Pages\Reports;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\LoanItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Buku';

    protected static ?string $title = 'Laporan Buku';

    protected static ?string $slug = 'report-books';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.reports.report';

    public ?array $data = [];

    public array $results = [];

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function form(Schema $schema): void
    {
        $schema->schema([
            Section::make()->schema([
                Placeholder::make('total_books')
                    ->label('Total Judul Buku')
                    ->content(fn () => (string) Book::count()),
                Placeholder::make('total_copies')
                    ->label('Total Eksemplar')
                    ->content(fn () => (string) BookCopy::count()),
                Placeholder::make('available')
                    ->label('Tersedia')
                    ->content(fn () => (string) BookCopy::where('status', 'available')->count()),
                Placeholder::make('borrowed')
                    ->label('Dipinjam')
                    ->content(fn () => (string) BookCopy::where('status', 'borrowed')->count()),
                Placeholder::make('damaged')
                    ->label('Rusak')
                    ->content(fn () => (string) BookCopy::where('status', 'damaged')->count()),
                Placeholder::make('lost')
                    ->label('Hilang')
                    ->content(fn () => (string) BookCopy::where('status', 'lost')->count()),
            ])->columns(3),
            Section::make('Buku Paling Sering Dipinjam')->schema([
                Placeholder::make('most_borrowed')
                    ->label('')
                    ->content(fn () => $this->getMostBorrowed()),
            ]),
        ])->statePath('data');
    }

    protected function getMostBorrowed(): string
    {
        $top = LoanItem::select('book_copy_id', \DB::raw('count(*) as total'))
            ->join('book_copies', 'loan_items.book_copy_id', '=', 'book_copies.id')
            ->groupBy('book_copy_id')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'book_copy_id');

        if ($top->isEmpty()) {
            return 'Belum ada data';
        }

        $lines = [];
        foreach ($top as $copyId => $count) {
            $copy = BookCopy::with('book')->find($copyId);
            if ($copy) {
                $lines[] = "{$copy->book->title} ({$copy->barcode}): {$count}x";
            }
        }

        return implode("\n", $lines) ?: 'Belum ada data';
    }

    public function generate(): void
    {
        Notification::make()->title('Data berhasil dimuat')->success()->send();
    }
}
