<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\InventoryResource\Pages;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class InventoryResource extends Resource
{
    protected static ?string $model = InventoryLog::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen';
    protected static ?string $navigationLabel = 'Inventaris';
    protected static ?string $modelLabel = 'riwayat inventaris';
    protected static ?string $pluralModelLabel = 'Inventaris';

    public static function canViewAny(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::STAFF, UserRole::SUPER_ADMIN], true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('info')->label('Riwayat inventaris dibuat melalui halaman pemeriksaan barcode.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('bookCopy.barcode')->label('Barcode')->searchable()->sortable(),
                TextColumn::make('bookCopy.book.title')->label('Buku')->searchable(),
                TextColumn::make('condition')->label('Kondisi')->badge(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('location')->label('Lokasi rak'),
                TextColumn::make('user.name')->label('Petugas'),
                TextColumn::make('created_at')->label('Diperiksa')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('condition')->options(['good' => 'Baik', 'minor_damage' => 'Rusak ringan', 'major_damage' => 'Rusak berat', 'lost' => 'Hilang']),
                SelectFilter::make('status')->options(['available' => 'Tersedia', 'reserved' => 'Direservasi', 'borrowed' => 'Dipinjam', 'damaged' => 'Rusak', 'lost' => 'Hilang', 'inactive' => 'Tidak aktif']),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInventories::route('/')];
    }
}
