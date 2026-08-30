<?php

namespace App\Filament\Pages\Reports;

use App\Models\Fine;
use App\Models\Loan;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OverdueReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Keterlambatan';

    protected static ?string $title = 'Laporan Keterlambatan';

    protected static ?string $slug = 'report-overdue';

    protected static ?int $navigationSort = 3;

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
                Placeholder::make('total_overdue')
                    ->label('Total Terlambat')
                    ->content(fn () => (string) Loan::where('status', 'borrowed')->where('due_at', '<', now())->count()),
                Placeholder::make('total_unpaid_fines')
                    ->label('Denda Belum Dibayar')
                    ->content(fn () => 'Rp'.number_format(Fine::where('status', 'unpaid')->sum('amount'), 0, ',', '.')),
                Placeholder::make('overdue_with_fines')
                    ->label('Pinjaman dengan Denda')
                    ->content(fn () => (string) Fine::where('status', 'unpaid')->distinct('loan_id')->count('loan_id')),
            ])->columns(3),
            Section::make('Detail Keterlambatan')->schema([
                Placeholder::make('overdue_list')
                    ->label('')
                    ->content(fn () => $this->getOverdueList()),
            ]),
        ])->statePath('data');
    }

    protected function getOverdueList(): string
    {
        $overdue = Loan::with(['student', 'items.bookCopy.book'])
            ->where('status', 'borrowed')
            ->where('due_at', '<', now())
            ->orderBy('due_at')
            ->get();

        if ($overdue->isEmpty()) {
            return 'Tidak ada pinjaman terlambat.';
        }

        $lines = [];
        foreach ($overdue as $loan) {
            $days = $loan->due_at->diffInDays(now());
            $studentName = $loan->student?->nama ?? '-';
            $bookTitle = $loan->items->first()?->bookCopy?->book?->title ?? '-';
            $lines[] = "{$loan->loan_number} | {$studentName} | {$bookTitle} | {$days} hari terlambat";
        }

        return implode("\n", $lines);
    }

    public function generate(): void
    {
        Notification::make()->title('Data berhasil dimuat')->success()->send();
    }
}
