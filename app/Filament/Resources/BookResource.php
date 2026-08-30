<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Perpustakaan';

    protected static ?string $navigationLabel = 'Buku';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('isbn')
                            ->label('ISBN')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('author')
                            ->label('Penulis')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('publisher')
                            ->label('Penerbit')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('publication_year')
                            ->label('Tahun Terbit')
                            ->required()
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(date('Y')),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('shelf_location')
                            ->label('Lokasi Rak')
                            ->maxLength(50),
                    ])->columns(2),
                Group::make()
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4),
                        Forms\Components\FileUpload::make('cover')
                            ->label('Cover')
                            ->image()
                            ->directory('book-covers')
                            ->nullable(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->label('Penulis')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('copies_count')
                    ->label('Eksemplar')
                    ->counts('copies')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('available_copies')
                    ->label('Tersedia')
                    ->state(fn (Book $record): int => $record->copies->where('status', 'available')->count())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('shelf_location')
                    ->label('Rak')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('publication_year')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Actions\Action::make('manage_copies')
                    ->label('Kelola Eksemplar')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (Book $record): string => route('filament.admin.resources.book-copies.index', ['book_id' => $record->id])),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
