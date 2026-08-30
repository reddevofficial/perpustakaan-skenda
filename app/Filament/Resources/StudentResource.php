<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Forms\Form;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | \UnitEnum | null $navigationGroup = 'Perpustakaan';

    protected static ?string $navigationLabel = 'Anggota';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Siswa';

    protected static ?string $pluralModelLabel = 'Siswa';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('nipd')
                            ->label('NIPD')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nisn')
                            ->label('NISN')
                            ->required()
                            ->maxLength(191)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\Select::make('jk')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required(),
                    ])->columns(2),
                Group::make()
                    ->schema([
                        Forms\Components\Select::make('rombel_id')
                            ->label('Kelas')
                            ->relationship('rombel', 'id', fn ($query) => $query->with(['tingkat', 'jurusan', 'indeks']))
                            ->formatStateUsing(fn ($state): string => $state ? $state->tingkat->nama.' '.$state->jurusan->nama.' '.($state->indeks?->nama ?? '') : '-')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('hp')
                            ->label('No. HP')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(191),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'aktif' => 'Aktif',
                                'nonaktif' => 'Nonaktif',
                                'diblokir' => 'Diblokir',
                            ])
                            ->default('aktif')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nipd')
                    ->label('NIPD')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rombel')
                    ->label('Kelas')
                    ->state(fn (Student $record): string => $record->class_name)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'nonaktif' => 'gray',
                        'diblokir' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('loans_count')
                    ->label('Pinjaman')
                    ->counts('loans')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                        'diblokir' => 'Diblokir',
                    ]),
            ])
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
