<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan Sistem';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'library_name' => setting('library_name', 'Perpustakaan SMKN 2 Banjarmasin'),
            'school_name' => setting('school_name', 'SMKN 2 Banjarmasin'),
            'school_address' => setting('school_address', ''),
            'school_phone' => setting('school_phone', ''),
            'loan_duration_days' => setting('loan_duration_days', 7),
            'max_books_per_student' => setting('max_books_per_student', 5),
            'max_extensions' => setting('max_extensions', 1),
            'extension_days' => setting('extension_days', 7),
            'fine_per_day' => setting('fine_per_day', 1000),
            'grace_period_days' => setting('grace_period_days', 0),
            'max_fine' => setting('max_fine', null),
            'reservation_expiry_days' => setting('reservation_expiry_days', 2),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Perpustakaan')->schema([
                    Forms\Components\TextInput::make('library_name')
                        ->label('Nama Perpustakaan')
                        ->required(),
                    Forms\Components\TextInput::make('school_name')
                        ->label('Nama Sekolah')
                        ->required(),
                    Forms\Components\TextInput::make('school_address')
                        ->label('Alamat'),
                    Forms\Components\TextInput::make('school_phone')
                        ->label('Telepon'),
                ])->columns(2),

                Forms\Components\Section::make('Pengaturan Peminjaman')->schema([
                    Forms\Components\TextInput::make('loan_duration_days')
                        ->label('Durasi Peminjaman (hari)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Forms\Components\TextInput::make('max_books_per_student')
                        ->label('Maksimal Buku per Siswa')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Forms\Components\TextInput::make('max_extensions')
                        ->label('Maksimal Perpanjangan')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('extension_days')
                        ->label('Durasi Perpanjangan (hari)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ])->columns(2),

                Forms\Components\Section::make('Pengaturan Denda')->schema([
                    Forms\Components\TextInput::make('fine_per_day')
                        ->label('Denda per Hari (Rp)')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('grace_period_days')
                        ->label('Grace Period (hari)')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('max_fine')
                        ->label('Maksimal Denda (Rp, kosongkan jika tidak terbatas)')
                        ->numeric()
                        ->minValue(0)
                        ->nullable(),
                ])->columns(3),

                Forms\Components\Section::make('Pengaturan Reservasi')->schema([
                    Forms\Components\TextInput::make('reservation_expiry_days')
                        ->label('Batas Pengambilan Reservasi (hari)')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                ])->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            setting_set($key, $value);
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}
