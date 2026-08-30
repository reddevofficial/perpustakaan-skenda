<?php

namespace App\Filament\Resources;

use App\Enums\LoanStatus;
use App\Filament\Resources\LoanResource\Pages;
use App\Models\Loan;
use App\Services\LoanService;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Peminjaman';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'Peminjaman';

    protected static ?string $pluralModelLabel = 'Peminjaman';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Placeholder::make('loan_number')
                    ->label('Nomor Peminjaman')
                    ->content(fn (?Loan $record): string => $record?->loan_number ?? '-'),
                Forms\Components\Placeholder::make('student_name')
                    ->label('Siswa')
                    ->content(fn (?Loan $record): string => $record?->student?->nama ?? '-'),
                Forms\Components\Placeholder::make('status_display')
                    ->label('Status')
                    ->content(fn (?Loan $record): string => $record?->status?->label() ?? '-'),
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Alasan Penolakan')
                    ->rows(3)
                    ->visible(fn (?Loan $record): bool => $record?->status === LoanStatus::REJECTED),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loan_number')
                    ->label('No. Pinjam')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.nama')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Buku')
                    ->counts('items')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LoanStatus $state): string => $state->label())
                    ->color(fn (LoanStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('due_at')
                    ->label('Jatuh Tempo')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->color(fn (?Loan $record): string => $record?->isOverdue() ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('requested_at')
                    ->label('Diajukan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(LoanStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Peminjaman')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui peminjaman ini?')
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::PENDING)
                    ->action(function (Loan $record) {
                        app(LoanService::class)->approve($record, auth()->id());
                        Notification::make()->title('Peminjaman disetujui')->success()->send();
                    }),
                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Peminjaman')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::PENDING)
                    ->action(function (Loan $record, array $data) {
                        app(LoanService::class)->reject($record, auth()->id(), $data['reason']);
                        Notification::make()->title('Peminjaman ditolak')->success()->send();
                    }),
                Actions\Action::make('mark_borrowed')
                    ->label('Serahkan Buku')
                    ->icon('heroicon-o-hand-raised')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Sudah Dipinjam')
                    ->visible(fn (Loan $record): bool => $record->status === LoanStatus::APPROVED)
                    ->action(function (Loan $record) {
                        app(LoanService::class)->markBorrowed($record);
                        Notification::make()->title('Buku sudah diserahkan')->success()->send();
                    }),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }
}
