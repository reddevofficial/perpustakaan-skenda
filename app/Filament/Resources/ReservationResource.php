<?php

namespace App\Filament\Resources;

use App\Enums\ReservationStatus;
use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Form;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string | \UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Reservasi';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Reservasi';

    protected static ?string $pluralModelLabel = 'Reservasi';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Forms\Components\Placeholder::make('reservation_number')
                    ->label('No. Reservasi')
                    ->content(fn (?Reservation $record): string => $record?->reservation_number ?? '-'),
                Forms\Components\Placeholder::make('student_name')
                    ->label('Siswa')
                    ->content(fn (?Reservation $record): string => $record?->student?->nama ?? '-'),
                Forms\Components\Placeholder::make('book_title')
                    ->label('Buku')
                    ->content(fn (?Reservation $record): string => $record?->book?->title ?? '-'),
                Forms\Components\Placeholder::make('queue')
                    ->label('Posisi Antrean')
                    ->content(fn (?Reservation $record): string => $record?->queue_position ? '#'.$record->queue_position : '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation_number')
                    ->label('No. Reservasi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.nama')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Buku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('queue_position')
                    ->label('Antrean')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ReservationStatus $state): string => $state->label())
                    ->color(fn (ReservationStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('reserved_at')
                    ->label('Direservasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ReservationStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
            ])
            ->actions([
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
            'index' => Pages\ListReservations::route('/'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
