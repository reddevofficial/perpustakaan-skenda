<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FineResource\Pages;
use App\Models\Fine;
use App\Services\FineService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class FineResource extends Resource
{
    protected static ?string $model = Fine::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Denda';

    protected static ?string $modelLabel = 'Denda';

    protected static ?string $pluralModelLabel = 'Denda';

    protected static ?string $slug = 'fines';

    public static function canAccess(): bool
    {
        return auth()->user()->isStaff();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Section::make('Detail Denda')->schema([
                Forms\Components\Placeholder::make('loan.loan_number')
                    ->label('Nomor Peminjaman')
                    ->content(fn (?Fine $record) => $record?->loan?->loan_number ?? '-'),
                Forms\Components\Placeholder::make('student.nama')
                    ->label('Siswa')
                    ->content(fn (?Fine $record) => $record?->student?->nama ?? '-'),
                Forms\Components\TextInput::make('amount')
                    ->label('Jumlah Denda')
                    ->prefix('Rp')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('late_days')
                    ->label('Hari Terlambat')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                        'waived' => 'Dibebaskan',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
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
                Tables\Columns\TextColumn::make('student.nama')
                    ->label('Siswa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Denda')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('late_days')
                    ->label('Hari')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'unpaid' => 'danger',
                        'paid' => 'success',
                        'waived' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                        'waived' => 'Dibebaskan',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Tanggal Bayar')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Sudah Dibayar',
                        'waived' => 'Dibebaskan',
                    ]),
            ])
            ->actions([
                Actions\Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Sudah Dibayar')
                    ->modalDescription('Konfirmasi bahwa denda ini telah dibayar.')
                    ->visible(fn (Fine $record) => $record->status->value === 'unpaid')
                    ->action(fn (Fine $record) => app(FineService::class)->pay($record, auth()->id())),
                Actions\Action::make('waive')
                    ->label('Bebaskan')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Bebaskan Denda')
                    ->modalDescription('Apakah Anda yakin ingin membebaskan denda ini?')
                    ->visible(fn (Fine $record) => $record->status->value === 'unpaid')
                    ->action(fn (Fine $record, array $data) => app(FineService::class)->waive($record, auth()->id(), $data['reason'])),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFines::route('/'),
            'create' => Pages\CreateFine::route('/create'),
            'edit' => Pages\EditFine::route('/{record}/edit'),
        ];
    }
}
