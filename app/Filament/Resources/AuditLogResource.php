<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Audit Log';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Log';

    protected static bool $shouldRecordNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'created') => 'success',
                        str_contains($state, 'deleted') => 'danger',
                        str_contains($state, 'updated') => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Model')
                    ->state(fn (AuditLog $record): string => class_basename($record->model_type))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('model_id')
                    ->label('ID')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Detail Audit Log')
                    ->schema([
                        TextEntry::make('action')->label('Aksi'),
                        TextEntry::make('user.name')->label('User'),
                        TextEntry::make('model_type')->label('Model'),
                        TextEntry::make('model_id')->label('ID'),
                        TextEntry::make('created_at')->label('Waktu')->dateTime(),
                        TextEntry::make('ip_address')->label('IP Address'),
                    ]),
                Section::make('Perubahan Data')
                    ->schema([
                        TextEntry::make('old_values')
                            ->label('Sebelum')
                            ->json(),
                        TextEntry::make('new_values')
                            ->label('Sesudah')
                            ->json(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
