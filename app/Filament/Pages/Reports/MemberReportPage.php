<?php

namespace App\Filament\Pages\Reports;

use App\Models\Loan;
use App\Models\Student;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Anggota';

    protected static ?string $title = 'Laporan Anggota';

    protected static ?string $slug = 'report-members';

    protected static ?int $navigationSort = 5;

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
                Placeholder::make('total_students')
                    ->label('Total Siswa')
                    ->content(fn () => (string) Student::count()),
                Placeholder::make('active_students')
                    ->label('Siswa Aktif')
                    ->content(fn () => (string) Student::where('status', 'aktif')->count()),
                Placeholder::make('students_with_loans')
                    ->label('Siswa dengan Pinjaman Aktif')
                    ->content(fn () => (string) Student::whereHas('loans', fn ($q) => $q->where('status', 'borrowed'))->count()),
                Placeholder::make('students_with_fines')
                    ->label('Siswa dengan Denda')
                    ->content(fn () => (string) Student::whereHas('fines', fn ($q) => $q->where('status', 'unpaid'))->count()),
            ])->columns(2),
            Section::make('Siswa Paling Aktif')->schema([
                Placeholder::make('most_active')
                    ->label('')
                    ->content(fn () => $this->getMostActive()),
            ]),
        ])->statePath('data');
    }

    protected function getMostActive(): string
    {
        $top = Loan::select('murid_id', \DB::raw('count(*) as total'))
            ->groupBy('murid_id')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'murid_id');

        if ($top->isEmpty()) {
            return 'Belum ada data';
        }

        $lines = [];
        foreach ($top as $studentId => $count) {
            $student = Student::find($studentId);
            if ($student) {
                $lines[] = "{$student->nama} ({$student->nipd}): {$count} peminjaman";
            }
        }

        return implode("\n", $lines) ?: 'Belum ada data';
    }

    public function generate(): void
    {
        Notification::make()->title('Data berhasil dimuat')->success()->send();
    }
}
