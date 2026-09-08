<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Models\Loan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use UnitEnum;

class ReportResource extends Resource
{
    protected static ?string $model = Loan::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $modelLabel = 'laporan peminjaman';
    protected static ?string $pluralModelLabel = 'Laporan';

    public static function canViewAny(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::STAFF, UserRole::SUPER_ADMIN], true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('report_link')->label('Gunakan halaman laporan untuk filter periode dan export CSV.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('loan_code')->label('Kode')->searchable(),
                TextColumn::make('student.user.name')->label('Anggota')->searchable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('due_at')->label('Jatuh tempo')->date('d M Y'),
                TextColumn::make('returned_at')->label('Dikembalikan')->dateTime('d M Y H:i'),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => ReportResource\Pages\ListReports::route('/')];
    }
}