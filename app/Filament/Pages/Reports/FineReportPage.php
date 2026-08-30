<?php

namespace App\Filament\Pages\Reports;

use App\Models\Fine;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class FineReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Denda';

    protected static ?string $title = 'Laporan Denda';

    protected static ?string $slug = 'report-fines';

    protected static ?int $navigationSort = 6;

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
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                        'waived' => 'Dibebaskan',
                    ])
                    ->nullable(),
            ])->columns(3),
            Section::make()->schema([
                Placeholder::make('total')
                    ->label('Total Denda')
                    ->content(fn () => (string) ($this->results['total'] ?? 0)),
                Placeholder::make('total_amount')
                    ->label('Total Nominal')
                    ->content(fn () => 'Rp'.number_format((float) ($this->results['total_amount'] ?? 0), 0, ',', '.')),
                Placeholder::make('unpaid_count')
                    ->label('Belum Dibayar')
                    ->content(fn () => (string) ($this->results['unpaid_count'] ?? 0)),
                Placeholder::make('unpaid_amount')
                    ->label('Nominal Belum Dibayar')
                    ->content(fn () => 'Rp'.number_format((float) ($this->results['unpaid_amount'] ?? 0), 0, ',', '.')),
            ])->columns(4),
        ])->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $query = Fine::query()
            ->whereBetween('created_at', [
                Carbon::parse($data['date_from'])->startOfDay(),
                Carbon::parse($data['date_to'])->endOfDay(),
            ]);

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        $all = $query->get();
        $unpaid = $all->where('status', 'unpaid');

        $this->results = [
            'total' => $all->count(),
            'total_amount' => $all->sum('amount'),
            'unpaid_count' => $unpaid->count(),
            'unpaid_amount' => $unpaid->sum('amount'),
        ];

        Notification::make()->title('Laporan berhasil digenerate')->success()->send();
    }
}
