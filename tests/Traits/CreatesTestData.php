<?php

namespace Tests\Traits;

use App\Enums\BookCopyStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

trait CreatesTestData
{
    protected function setUpBaseTables(): void
    {
        if (! Schema::hasTable('murid')) {
            Schema::create('murid', function ($table) {
                $table->id();
                $table->string('nipd')->unique();
                $table->string('nama');
                $table->date('tanggal_lahir')->nullable();
                $table->string('jenis_kelamin', 1)->nullable();
                $table->text('alamat')->nullable();
                $table->string('telepon')->nullable();
                $table->string('email')->nullable();
                $table->foreignId('rombel_id')->nullable();
                $table->foreignId('tingkat_id')->nullable();
                $table->foreignId('jurusan_id')->nullable();
                $table->foreignId('indeks_id')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('role')) {
            Schema::create('role', function ($table) {
                $table->id();
                $table->string('name');
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function ($table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    protected function createRoles(): array
    {
        $now = now()->toDateTimeString();

        DB::table('role')->insertOrIgnore([
            ['id' => 1, 'name' => 'Super Admin', 'permissions' => json_encode(['*']), 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Petugas', 'permissions' => json_encode([
                'catalog-view', 'book-manage', 'category-manage', 'student-view',
                'loan-manage', 'loan-approve', 'return-process', 'reservation-manage',
                'report-view', 'audit-view',
            ]), 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'Siswa', 'permissions' => json_encode([
                'catalog-view', 'loan-request', 'loan-view-own',
                'reservation-create', 'reservation-view-own',
                'notification-view-own', 'profile-view-own',
            ]), 'created_at' => $now, 'updated_at' => $now],
        ]);

        $superAdmin = Role::find(1);
        $staff = Role::find(6);
        $student = Role::find(7);

        return compact('superAdmin', 'staff', 'student');
    }

    protected function createStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'nipd' => fake()->unique()->numerify('1#####'),
            'nama' => fake()->name(),
            'tanggal_lahir' => fake()->dateTimeBetween('-18 years', '-15 years'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'alamat' => fake()->address(),
            'telepon' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'status' => 'active',
        ], $overrides));
    }

    protected function createStaff(array $overrides = []): User
    {
        $roles = $this->createRoles();

        return User::create(array_merge([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'is_active' => true,
            'role_id' => $roles['staff']->id,
        ], $overrides));
    }

    protected function createSuperAdmin(array $overrides = []): User
    {
        $roles = $this->createRoles();

        return User::create(array_merge([
            'name' => 'Super Admin',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'is_active' => true,
            'role_id' => $roles['superAdmin']->id,
        ], $overrides));
    }

    protected function createStudentUser(Student $student, array $overrides = []): User
    {
        $roles = $this->createRoles();

        return User::create(array_merge([
            'name' => $student->nama,
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'is_active' => true,
            'role_id' => $roles['student']->id,
            'student_id' => $student->id,
        ], $overrides));
    }

    protected function createCategory(array $overrides = []): Category
    {
        return Category::create(array_merge([
            'name' => fake()->word(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
        ], $overrides));
    }

    protected function createBook(Category $category, array $overrides = []): Book
    {
        return Book::create(array_merge([
            'isbn' => fake()->unique()->isbn13(),
            'title' => fake()->words(3, true),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'publication_year' => fake()->year(),
            'category_id' => $category->id,
            'shelf_location' => strtoupper(fake()->randomLetter()).'-'.fake()->numerify('##'),
        ], $overrides));
    }

    protected function createBookCopy(Book $book, string|BookCopyStatus $status = 'available', array $overrides = []): BookCopy
    {
        return BookCopy::create(array_merge([
            'book_id' => $book->id,
            'barcode' => 'BK-'.str_pad(fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'status' => $status instanceof BookCopyStatus ? $status->value : $status,
            'condition' => 'Baik',
            'shelf_location' => $book->shelf_location,
        ], $overrides));
    }

    protected function createLoan(Student $student, string $status = 'pending', array $overrides = []): Loan
    {
        return Loan::create(array_merge([
            'loan_number' => Loan::generateLoanNumber(),
            'murid_id' => $student->id,
            'status' => $status,
            'requested_at' => now(),
        ], $overrides));
    }

    protected function createLoanItem(Loan $loan, BookCopy $copy, string $status = 'pending', array $overrides = []): LoanItem
    {
        return LoanItem::create(array_merge([
            'loan_id' => $loan->id,
            'book_copy_id' => $copy->id,
            'due_at' => now()->addDays(7),
            'status' => $status,
        ], $overrides));
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['key' => 'loan_duration_days', 'value' => '7'],
            ['key' => 'max_books_per_student', 'value' => '5'],
            ['key' => 'max_extensions', 'value' => '1'],
            ['key' => 'extension_days', 'value' => '7'],
            ['key' => 'fine_per_day', 'value' => '1000'],
            ['key' => 'grace_period_days', 'value' => '0'],
            ['key' => 'max_fine', 'value' => null],
            ['key' => 'reservation_expiry_days', 'value' => '2'],
        ];

        foreach ($settings as $setting) {
            setting_set($setting['key'], $setting['value']);
        }
    }
}
