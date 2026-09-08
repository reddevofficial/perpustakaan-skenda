<?php

namespace App\Filament\Resources;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use App\Services\FineService;
use App\Services\LoanService;
use App\Services\ReservationService;
use App\Services\ReturnBookService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use RuntimeException;
use UnitEnum;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen';
    protected static ?string $navigationLabel = 'Peminjaman';
    protected static ?string $modelLabel = 'peminjaman';
    protected static ?string $pluralModelLabel = 'Peminjaman';

    public static function canViewAny(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::STAFF, UserRole::SUPER_ADMIN], true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('loan_code')->label('Kode transaksi')->searchable()->sortable(),
                TextColumn::make('student.student_number')->label('NIS')->searchable(),
                TextColumn::make('student.user.name')->label('Anggota')->searchable()->sortable(),
                TextColumn::make('items.bookCopy.book.title')
                    ->label('Buku')
                    ->getStateUsing(fn (Loan $record): string => $record->items->pluck('bookCopy.book.title')->filter()->join(', '))
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (LoanStatus|string|null $state): string => $state instanceof LoanStatus ? match ($state) {
                    LoanStatus::PENDING => 'Menunggu persetujuan',
                    LoanStatus::APPROVED => 'Disetujui',
                    LoanStatus::BORROWED => 'Dipinjam',
                    LoanStatus::OVERDUE => 'Terlambat',
                    LoanStatus::RETURNED => 'Dikembalikan',
                    LoanStatus::REJECTED => 'Ditolak',
                    LoanStatus::CANCELLED => 'Dibatalkan',
                    LoanStatus::LOST => 'Hilang',
                    LoanStatus::DRAFT => 'Draft',
                } : (string) $state),
                TextColumn::make('requested_at')->label('Diajukan')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('due_at')->label('Jatuh tempo')->date('d M Y')->sortable(),
                TextColumn::make('returned_at')->label('Dikembalikan')->dateTime('d M Y H:i'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(collect(LoanStatus::cases())->mapWithKeys(fn (LoanStatus $status): array => [$status->value => match ($status) {
                    LoanStatus::PENDING => 'Menunggu persetujuan',
                    LoanStatus::APPROVED => 'Disetujui',
                    LoanStatus::BORROWED => 'Dipinjam',
                    LoanStatus::OVERDUE => 'Terlambat',
                    LoanStatus::RETURNED => 'Dikembalikan',
                    LoanStatus::REJECTED => 'Ditolak',
                    LoanStatus::CANCELLED => 'Dibatalkan',
                    LoanStatus::LOST => 'Hilang',
                    LoanStatus::DRAFT => 'Draft',
                }])->all()),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::PENDING)
                    ->action(function (Loan $record): void {
                        try {
                            app(LoanService::class)->approve($record, Auth::id());
                        } catch (RuntimeException $exception) {
                            throw new \Exception($exception->getMessage(), previous: $exception);
                        }
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([Textarea::make('reason')->label('Alasan penolakan')->required()->maxLength(1000)])
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::PENDING)
                    ->action(function (Loan $record, array $data): void {
                        app(LoanService::class)->reject($record, Auth::id(), $data['reason']);
                    }),
                Action::make('handover')
                    ->label('Serahkan buku')
                    ->icon('heroicon-o-hand-raised')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::APPROVED)
                    ->action(fn (Loan $record): Loan => app(LoanService::class)->markBorrowed($record)),
                Action::make('return')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->form([Textarea::make('notes')->label('Catatan pengembalian')->maxLength(1000)])
                    ->visible(fn (Loan $record): bool => in_array($record->status, [LoanStatus::BORROWED, LoanStatus::OVERDUE], true))
                    ->action(fn (Loan $record, array $data): Loan => app(ReturnBookService::class)->return(
                        $record,
                        app(FineService::class),
                        app(ReservationService::class),
                        $data['notes'] ?? null,
                    )),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn (): bool => Auth::user()?->role === UserRole::SUPER_ADMIN),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
        ];
    }
}
