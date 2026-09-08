<?php

namespace Database\Seeders;

use App\Enums\BookCopyStatus;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@perpustakaan.test'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => UserRole::SUPER_ADMIN,
                'status' => 'active',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'petugas@perpustakaan.test'],
            [
                'name' => 'Petugas Perpustakaan',
                'username' => 'petugas',
                'password' => Hash::make('password'),
                'role' => UserRole::STAFF,
                'status' => 'active',
            ],
        );

        $categoryNames = ['Pelajaran', 'Fiksi', 'Non-Fiksi', 'Teknologi', 'Sejarah', 'Agama', 'Sains', 'Novel', 'Referensi', 'Bahasa'];
        $categories = collect($categoryNames)->mapWithKeys(fn (string $name): array => [
            $name => Category::query()->updateOrCreate(['name' => $name], ['slug' => str()->slug($name), 'is_active' => true]),
        ]);

        User::factory(10)->create()->each(function (User $user): void {
            Student::query()->create([
                'user_id' => $user->id,
                'student_number' => 'NIS-'.$user->id,
                'class' => 'XI',
                'major' => 'Rekayasa Perangkat Lunak',
                'status' => 'active',
                'joined_at' => now()->toDateString(),
            ]);
        });

        foreach (range(1, 20) as $number) {
            $book = Book::query()->firstOrCreate(
                ['book_code' => 'BK-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT)],
                [
                    'category_id' => $categories->values()->get(($number - 1) % $categories->count())->id,
                    'title' => 'Koleksi Perpustakaan '.$number,
                    'author' => 'Penulis Contoh '.$number,
                    'publisher' => 'Penerbit Sekolah',
                    'publication_year' => 2020 + ($number % 5),
                    'status' => 'active',
                ],
            );

            BookCopy::query()->firstOrCreate(
                ['barcode' => 'BC-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT)],
                ['book_id' => $book->id, 'status' => BookCopyStatus::AVAILABLE, 'condition' => 'good'],
            );

            $book->update([
                'stock' => $book->copies()->count(),
                'available_stock' => $book->copies()->where('status', BookCopyStatus::AVAILABLE->value)->count(),
            ]);
        }

        foreach ([
            ['key' => 'loan_duration_days', 'value' => '7', 'type' => 'integer'],
            ['key' => 'max_active_loans', 'value' => '3', 'type' => 'integer'],
            ['key' => 'max_extensions', 'value' => '1', 'type' => 'integer'],
            ['key' => 'extension_duration_days', 'value' => '7', 'type' => 'integer'],
            ['key' => 'fine_daily_rate', 'value' => '1000', 'type' => 'integer'],
            ['key' => 'reservation_pickup_hours', 'value' => '48', 'type' => 'integer'],
        ] as $setting) {
            SystemSetting::query()->updateOrCreate(['key' => $setting['key']], $setting);
        }

        $admin->forceFill(['email_verified_at' => now()])->save();
    }
}
