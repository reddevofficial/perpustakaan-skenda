<?php

namespace App\Filament\Pages\Reports;

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

class ReturnReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Pengembalian';

    protected static ?string $title = 'Laporan Pengembalian';

    protected static ?string $slug = 'report-returns';

    protected static ?int $navigationSort = 2;

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
                Forms\Components\Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'all' => 'Semua',
                        'on_time' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                    ])
                    ->default('all'),
            ])->columns(3),
            Section::make()->schema([
                Placeholder::make('total')
                    ->label('Total Pengembalian')
                    ->content(fn () => (string) ($this->results['total'] ?? 0)),
                Placeholder::make('on_time')
                    ->label('Tepat Waktu')
                    ->content(fn () => (string) ($this->results['on_time'] ?? 0)),
                Placeholder::make('late')
                    ->label('Terlambat')
                    ->content(fn () => (string) ($this->results['late'] ?? 0)),
            ])->columns(3),
        ])->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $query = Loan::whereNotNull('returned_at')
            ->whereBetween('returned_at', [
                Carbon::parse($data['date_from'])->startOfDay(),
                Carbon::parse($data['date_to'])->endOfDay(),
            ]);

        $all = $query->get();
        $onTime = $all->filter(fn ($loan) => $loan->due_at && $loan->returned_at->lte($loan->due_at));

        $this->results = [
            'total' => $all->count(),
            'on_time' => $onTime->count(),
            'late' => $all->count() - $onTime->count(),
        ];

        Notification::make()->title('Laporan berhasil digenerate')->success()->send();
    }
}
