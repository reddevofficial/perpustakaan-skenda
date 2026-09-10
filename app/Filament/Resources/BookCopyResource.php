<?php

namespace App\Filament\Resources;

use App\Enums\BookCopyStatus;
use App\Enums\UserRole;
use App\Filament\Resources\BookCopyResource\Pages;
use Illuminate\Support\Facades\Auth;
use App\Models\BookCopy;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;
use Illuminate\Support\Collection;

class BookCopyResource extends Resource
{
    protected static ?string $model = BookCopy::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Eksemplar Buku';
    protected static ?string $modelLabel = 'eksemplar buku';
    protected static ?string $pluralModelLabel = 'Eksemplar Buku';
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen';

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->role !== UserRole::STUDENT;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('book_id')->label('Buku')->relationship('book', 'title')->searchable()->preload()->required(),
            TextInput::make('barcode')->label('Barcode')->required()->unique(ignoreRecord: true)->maxLength(100),
            Select::make('condition')->label('Kondisi')->options([
                'good' => 'Baik', 'minor_damage' => 'Rusak Ringan', 'major_damage' => 'Rusak Berat', 'lost' => 'Hilang',
            ])->required()->default('good'),
            Select::make('status')->label('Status')->options(collect(BookCopyStatus::cases())->mapWithKeys(fn (BookCopyStatus $status): array => [$status->value => match ($status) {
                BookCopyStatus::AVAILABLE => 'Tersedia',
                BookCopyStatus::RESERVED => 'Direservasi',
                BookCopyStatus::BORROWED => 'Dipinjam',
                BookCopyStatus::DAMAGED => 'Rusak',
                BookCopyStatus::LOST => 'Hilang',
                BookCopyStatus::INACTIVE => 'Tidak Aktif',
            }])->all())->required()->default(BookCopyStatus::AVAILABLE->value)->disabled(fn (string $operation): bool => $operation === 'edit'),
            TextInput::make('shelf_location')->label('Lokasi rak')->maxLength(100),
            Textarea::make('notes')->label('Catatan')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('barcode')->label('Barcode')->searchable()->sortable(),
                TextColumn::make('book.title')->label('Judul buku')->searchable()->sortable(),
                TextColumn::make('condition')->label('Kondisi')->badge(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('shelf_location')->label('Lokasi rak')->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(collect(BookCopyStatus::cases())->mapWithKeys(fn (BookCopyStatus $status): array => [$status->value => $status->name])->all()),
                SelectFilter::make('book')->label('Buku')->relationship('book', 'title'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([
                BulkAction::make('printLabels')
                    ->label('Cetak label')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Collection $records): string => route('book-labels.index', ['ids' => $records->modelKeys()]))
                    ->deselectRecordsAfterCompletion(),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookCopies::route('/'),
            'create' => Pages\CreateBookCopy::route('/create'),
            'edit' => Pages\EditBookCopy::route('/{record}/edit'),
        ];
    }
}
