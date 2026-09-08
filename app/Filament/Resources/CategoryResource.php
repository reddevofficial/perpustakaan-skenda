<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use BackedEnum;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori';
    protected static ?string $modelLabel = 'kategori';
    protected static ?string $pluralModelLabel = 'Kategori';
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen';

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->role !== UserRole::STUDENT;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama kategori')->required()->maxLength(150),
            TextInput::make('slug')->label('Slug')->maxLength(150)->unique(ignoreRecord: true),
            Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->searchable(),
                TextColumn::make('books_count')->label('Jumlah buku')->counts('books')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Status')])
            ->actions([EditAction::make(), DeleteAction::make()->before(function (Category $record): void {
                abort_if($record->books()->exists(), 422, 'Kategori masih digunakan oleh buku.');
            })])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
