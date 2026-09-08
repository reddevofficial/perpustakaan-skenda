<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku';
    protected static ?string $modelLabel = 'buku';
    protected static ?string $pluralModelLabel = 'Buku';
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen';

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->role !== UserRole::STUDENT;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('book_code')->label('ID Buku')->disabled()->dehydrated(false),
            TextInput::make('isbn')->label('ISBN')->maxLength(30),
            TextInput::make('title')->label('Judul')->required()->maxLength(255),
            TextInput::make('author')->label('Penulis')->required()->maxLength(255),
            TextInput::make('publisher')->label('Penerbit')->maxLength(255),
            TextInput::make('publication_year')->label('Tahun terbit')->numeric()->minValue(1000)->maxValue((int) date('Y')),
            Select::make('category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
            TextInput::make('stock')->label('Jumlah stok')->numeric()->minValue(0)->default(0)->required()->live()->disabled(fn (string $operation): bool => $operation === 'edit'),
            TextInput::make('available_stock')->label('Stok tersedia')->numeric()->minValue(0)->lte('stock')->default(0)->required()->disabled(fn (string $operation): bool => $operation === 'edit'),
            TextInput::make('shelf_location')->label('Lokasi rak')->maxLength(100),
            Select::make('status')->label('Status')->options([
                'active' => 'Tersedia', 'inactive' => 'Tidak Aktif', 'damaged' => 'Rusak', 'lost' => 'Hilang',
            ])->required()->default('active'),
            FileUpload::make('cover')->label('Cover')->image()->disk('public')->directory('books')->imageEditor(),
            Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')->label('Cover')->circular(),
                TextColumn::make('book_code')->label('ID Buku')->searchable()->sortable(),
                TextColumn::make('title')->label('Judul')->searchable()->sortable()->limit(40),
                TextColumn::make('author')->label('Penulis')->searchable(),
                TextColumn::make('category.name')->label('Kategori')->sortable(),
                TextColumn::make('stock')->label('Stok')->sortable(),
                TextColumn::make('available_stock')->label('Tersedia')->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->filters([
                SelectFilter::make('category')->label('Kategori')->relationship('category', 'name'),
                SelectFilter::make('status')->label('Status')->options(['active' => 'Aktif', 'inactive' => 'Tidak aktif', 'damaged' => 'Rusak', 'lost' => 'Hilang']),
                TernaryFilter::make('available_stock')->label('Memiliki stok')->queries(
                    true: fn ($query) => $query->where('available_stock', '>', 0),
                    false: fn ($query) => $query->where('available_stock', 0),
                ),
            ])
            ->actions([ViewAction::make(), EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'view' => Pages\ViewBook::route('/{record}'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
