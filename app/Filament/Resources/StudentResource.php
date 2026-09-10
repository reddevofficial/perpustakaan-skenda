<?php

namespace App\Filament\Resources;

use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Anggota';
    protected static ?string $modelLabel = 'anggota';
    protected static ?string $pluralModelLabel = 'Anggota';
    protected static string|UnitEnum|null $navigationGroup = 'Manajemen';

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->role !== UserRole::STUDENT;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->label('Akun pengguna')->relationship('user', 'name', modifyQueryUsing: fn ($query) => $query->where('role', UserRole::STUDENT->value))->searchable()->preload()->required()->unique(ignoreRecord: true),
            TextInput::make('student_number')->label('NIS')->required()->unique(ignoreRecord: true)->maxLength(50),
            TextInput::make('class')->label('Kelas')->maxLength(50),
            TextInput::make('major')->label('Jurusan')->maxLength(100),
            TextInput::make('phone')->label('Nomor HP')->tel()->maxLength(30),
            Select::make('status')->label('Status')->options(collect(MemberStatus::cases())->mapWithKeys(fn (MemberStatus $status): array => [$status->value => match ($status) {
                MemberStatus::ACTIVE => 'Aktif',
                MemberStatus::INACTIVE => 'Tidak Aktif',
                MemberStatus::BLOCKED => 'Diblokir',
            }])->all())->required()->default(MemberStatus::ACTIVE->value),
            DatePicker::make('joined_at')->label('Tanggal bergabung')->default(now()),
            Textarea::make('address')->label('Alamat')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_number')->label('NIS')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('class')->label('Kelas')->sortable(),
                TextColumn::make('major')->label('Jurusan')->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('joined_at')->label('Bergabung')->date('d M Y')->sortable(),
            ])
            ->filters([SelectFilter::make('status')->label('Status')->options(collect(MemberStatus::cases())->mapWithKeys(fn (MemberStatus $status): array => [$status->value => $status->name])->all())])
            ->actions([ViewAction::make(), EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
