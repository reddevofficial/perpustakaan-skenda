<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Activity Log';
    protected static ?string $modelLabel = 'activity log';
    protected static ?string $pluralModelLabel = 'Activity Log';

    public static function canViewAny(): bool
    {
        return Auth::user()?->role === UserRole::SUPER_ADMIN;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('readonly')->label('Activity log bersifat hanya-baca.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Pengguna')->searchable()->placeholder('Sistem'),
                TextColumn::make('action')->label('Aksi')->badge(),
                TextColumn::make('subject_type')->label('Model')->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                TextColumn::make('subject_id')->label('ID data')->sortable(),
                TextColumn::make('description')->label('Deskripsi')->wrap(),
                TextColumn::make('ip_address')->label('IP'),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s')->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')->options(['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus']),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListActivityLogs::route('/')];
    }
}
