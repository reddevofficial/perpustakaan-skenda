<?php

namespace App\Filament\Resources;

use App\Enums\ExtensionStatus;
use App\Filament\Resources\LoanExtensionResource\Pages;
use App\Models\LoanExtension;
use App\Services\LoanService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoanExtensionResource extends Resource
{
    protected static ?string $model = LoanExtension::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Perpanjangan';

    protected static ?string $modelLabel = 'Perpanjangan';

    protected static ?string $pluralModelLabel = 'Perpanjangan';

    protected static ?string $slug = 'loan-extensions';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return auth()->user()->isStaff();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Section::make('Detail Perpanjangan')->schema([
                Forms\Components\Placeholder::make('loan.loan_number')
                    ->label('Nomor Peminjaman')
                    ->content(fn (?LoanExtension $record) => $record?->loan?->loan_number ?? '-'),
                Forms\Components\Placeholder::make('loan.student.nama')
                    ->label('Siswa')
                    ->content(fn (?LoanExtension $record) => $record?->loan?->student?->nama ?? '-'),
                Forms\Components\Placeholder::make('old_due_at')
                    ->label('Jatuh Tempo Lama')
                    ->content(fn (?LoanExtension $record) => $record?->old_due_at?->format('d M Y H:i') ?? '-'),
                Forms\Components\Placeholder::make('new_due_at')
                    ->label('Jatuh Tempo Baru')
                    ->content(fn (?LoanExtension $record) => $record?->new_due_at?->format('d M Y H:i') ?? '-'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(ExtensionStatus::class)
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->label('Alasan')
                    ->rows(3),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('loan.loan_number')
                    ->label('Pinjaman')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loan.student.nama')
                    ->label('Siswa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('old_due_at')
                    ->label('Jatuh Tempo Lama')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_due_at')
                    ->label('Jatuh Tempo Baru')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ExtensionStatus $state) => $state->label())
                    ->color(fn (ExtensionStatus $state) => $state->color()),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Diminta Oleh'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(ExtensionStatus::class),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Perpanjangan')
                    ->visible(fn (LoanExtension $record) => $record->status === ExtensionStatus::PENDING)
                    ->action(fn (LoanExtension $record) => app(LoanService::class)->approveExtension($record, auth()->id())),
                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Perpanjangan')
                    ->visible(fn (LoanExtension $record) => $record->status === ExtensionStatus::PENDING)
                    ->action(fn (LoanExtension $record) => app(LoanService::class)->rejectExtension($record, auth()->id())),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanExtensions::route('/'),
            'edit' => Pages\EditLoanExtension::route('/{record}/edit'),
        ];
    }
}
