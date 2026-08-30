<?php

namespace App\Filament\Pages\Reports;

use App\Models\Category;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class LoanReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Peminjaman';

    protected static ?string $title = 'Laporan Peminjaman';

    protected static ?string $slug = 'report-loans';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports.report';

    public ?array $data = [];

    public array $results = [];

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $schema): void
    {
        $schema->schema([
            Section::make('Filter')->schema([
                Forms\Components\DatePicker::make('date_from')
                    ->label('Dari Tanggal')
                    ->required(),
                Forms\Components\DatePicker::make('date_to')
                    ->label('Sampai Tanggal')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'borrowed' => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'rejected' => 'Ditolak',
                        'overdue' => 'Terlambat',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->nullable(),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->options(Category::pluck('name', 'id'))
                    ->nullable(),
            ])->columns(4),
            Section::make()->schema([
                Placeholder::make('total')
                    ->label('Total Peminjaman')
                    ->content(fn () => (string) ($this->results['total'] ?? 0)),
                Placeholder::make('active')
                    ->label('Aktif')
                    ->content(fn () => (string) ($this->results['active'] ?? 0)),
                Placeholder::make('returned')
                    ->label('Dikembalikan')
                    ->content(fn () => (string) ($this->results['returned'] ?? 0)),
                Placeholder::make('overdue')
                    ->label('Terlambat')
                    ->content(fn () => (string) ($this->results['overdue'] ?? 0)),
            ])->columns(4),
        ])->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $query = Loan::query()
            ->whereBetween('created_at', [
                Carbon::parse($data['date_from'])->startOfDay(),
                Carbon::parse($data['date_to'])->endOfDay(),
            ]);

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (! empty($data['category_id'])) {
            $query->whereHas('items.bookCopy.book', fn ($q) => $q->where('category_id', $data['category_id']));
        }

        $all = $query->get();

        $this->results = [
            'total' => $all->count(),
            'active' => $all->whereIn('status', ['pending', 'approved', 'borrowed'])->count(),
            'returned' => $all->where('status', 'returned')->count(),
            'overdue' => $all->where('status', 'overdue')->count(),
        ];

        Notification::make()->title('Laporan berhasil digenerate')->success()->send();
    }
}
