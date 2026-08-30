<?php

namespace App\Filament\Resources;

use App\Enums\BookCopyStatus;
use App\Filament\Resources\BookCopyResource\Pages;
use App\Models\Book;
use App\Models\BookCopy;
use App\Services\BarcodeService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Form;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class BookCopyResource extends Resource
{
    protected static ?string $model = BookCopy::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-qr-code';

    protected static string | \UnitEnum | null $navigationGroup = 'Perpustakaan';

    protected static ?string $navigationLabel = 'Eksemplar';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('Buku')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('barcode')
                    ->label('Barcode')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->default(fn () => app(BarcodeService::class)->generateForBook(
                        Book::first()
                    )),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(collect(BookCopyStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->default(BookCopyStatus::AVAILABLE->value)
                    ->required(),
                Forms\Components\Select::make('condition')
                    ->label('Kondisi')
                    ->options([
                        'Baik' => 'Baik',
                        'Rusak Ringan' => 'Rusak Ringan',
                        'Rusak Berat' => 'Rusak Berat',
                    ])
                    ->default('Baik')
                    ->required(),
                Forms\Components\TextInput::make('shelf_location')
                    ->label('Lokasi Rak')
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('book.title')
                    ->label('Buku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (BookCopyStatus $state): string => $state->label())
                    ->color(fn (BookCopyStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak Ringan' => 'warning',
                        'Rusak Berat' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('shelf_location')
                    ->label('Rak'),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListBookCopies::route('/'),
            'create' => Pages\CreateBookCopy::route('/create'),
            'edit' => Pages\EditBookCopy::route('/{record}/edit'),
        ];
    }
}
