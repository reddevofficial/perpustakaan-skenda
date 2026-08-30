<?php

namespace Database\Seeders;

use App\Enums\BookCopyStatus;
use App\Enums\LoanItemStatus;
use App\Enums\LoanStatus;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();
        $this->seedSettings();
        $this->seedUsers();
        $this->seedCategories();
        $this->seedBooks();
        $this->seedTestLoans();
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['key' => 'library_name', 'value' => 'Perpustakaan SMKN 2 Banjarmasin', 'group' => 'general'],
            ['key' => 'school_name', 'value' => 'SMKN 2 Banjarmasin', 'group' => 'general'],
            ['key' => 'school_address', 'value' => 'Jl. Pangeran Antasari, Banjarmasin', 'group' => 'general'],
            ['key' => 'school_phone', 'value' => '0511-123456', 'group' => 'general'],
            ['key' => 'loan_duration_days', 'value' => '7', 'group' => 'loan'],
            ['key' => 'max_books_per_student', 'value' => '5', 'group' => 'loan'],
            ['key' => 'max_extensions', 'value' => '1', 'group' => 'loan'],
            ['key' => 'extension_days', 'value' => '7', 'group' => 'loan'],
            ['key' => 'fine_per_day', 'value' => '1000', 'group' => 'fine'],
            ['key' => 'grace_period_days', 'value' => '0', 'group' => 'fine'],
            ['key' => 'max_fine', 'value' => null, 'group' => 'fine'],
            ['key' => 'reservation_expiry_days', 'value' => '2', 'group' => 'reservation'],
        ];

        foreach ($settings as $setting) {
            setting_set($setting['key'], $setting['value']);
        }
    }

    protected function seedRoles(): void
    {
        Role::updateOrCreate(['id' => 6], [
            'name' => 'Petugas',
            'permissions' => [
                'catalog-view', 'book-manage', 'category-manage', 'student-view',
                'loan-manage', 'loan-approve', 'return-process', 'reservation-manage',
                'report-view', 'audit-view',
            ],
        ]);

        Role::updateOrCreate(['id' => 7], [
            'name' => 'Siswa',
            'permissions' => [
                'catalog-view', 'loan-request', 'loan-view-own',
                'reservation-create', 'reservation-view-own',
                'notification-view-own', 'profile-view-own',
            ],
        ]);
    }

    protected function seedUsers(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@skenda.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role_id' => 1,
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@skenda.com'],
            [
                'name' => 'Petugas Perpustakaan',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role_id' => 6,
            ]
        );

        $student = Student::where('nipd', '12088')->first();
        if ($student) {
            User::updateOrCreate(
                ['email' => 'siswa@skenda.com'],
                [
                    'name' => $student->nama,
                    'student_id' => $student->id,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'role_id' => 7,
                ]
            );
        }
    }

    protected function seedCategories(): void
    {
        $categories = [
            ['name' => 'Fiksi', 'slug' => 'fiksi', 'description' => 'Novel dan cerita fiksi'],
            ['name' => 'Non-Fiksi', 'slug' => 'non-fiksi', 'description' => 'Buku informasi dan pengetahuan'],
            ['name' => 'Sains & Teknologi', 'slug' => 'sains-teknologi', 'description' => 'Buku sains dan teknologi'],
            ['name' => 'Sejarah', 'slug' => 'sejarah', 'description' => 'Buku sejarah'],
            ['name' => 'Agama', 'slug' => 'agama', 'description' => 'Buku keagamaan'],
            ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'description' => 'Buku pendidikan dan belajar'],
            ['name' => 'Komik', 'slug' => 'komik', 'description' => 'Komik dan manga'],
            ['name' => 'Referensi', 'slug' => 'referensi', 'description' => 'Ensiklopedia dan kamus'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }

    protected function seedBooks(): void
    {
        $books = [
            [
                'isbn' => '978-602-000-001-1',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'publication_year' => 2005,
                'category_slug' => 'fiksi',
                'shelf_location' => 'A-01',
                'copies' => 3,
            ],
            [
                'isbn' => '978-602-000-002-8',
                'title' => 'Bumi',
                'author' => 'Tere Liye',
                'publisher' => 'Gramedia Pustaka Utama',
                'publication_year' => 2014,
                'category_slug' => 'fiksi',
                'shelf_location' => 'A-02',
                'copies' => 2,
            ],
            [
                'isbn' => '978-602-000-003-5',
                'title' => 'Filosofi Teras',
                'author' => 'Henry Manampiring',
                'publisher' => 'Kompas Gramedia',
                'publication_year' => 2018,
                'category_slug' => 'non-fiksi',
                'shelf_location' => 'B-01',
                'copies' => 2,
            ],
            [
                'isbn' => '978-602-000-004-2',
                'title' => 'Sapiens: A Brief History of Humankind',
                'author' => 'Yuval Noah Harari',
                'publisher' => 'KPG (Kepustakaan Populer Gramedia)',
                'publication_year' => 2014,
                'category_slug' => 'sejarah',
                'shelf_location' => 'C-01',
                'copies' => 2,
            ],
            [
                'isbn' => '978-602-000-005-9',
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'publisher' => 'Gramedia Pustaka Utama',
                'publication_year' => 2018,
                'category_slug' => 'non-fiksi',
                'shelf_location' => 'B-02',
                'copies' => 3,
            ],
            [
                'isbn' => '978-602-000-006-6',
                'title' => 'Negeri 5 Menara',
                'author' => 'Ahmad Fuadi',
                'publisher' => 'Gramedia Pustaka Utama',
                'publication_year' => 2009,
                'category_slug' => 'fiksi',
                'shelf_location' => 'A-03',
                'copies' => 2,
            ],
            [
                'isbn' => '978-602-000-007-3',
                'title' => 'Pemrograman Laravel',
                'author' => 'Jufri Zahri',
                'publisher' => 'Elex Media Komputindo',
                'publication_year' => 2020,
                'category_slug' => 'sains-teknologi',
                'shelf_location' => 'D-01',
                'copies' => 2,
            ],
            [
                'isbn' => null,
                'title' => 'Bahasa Indonesia Kelas X',
                'author' => 'Tim Penulis',
                'publisher' => 'Kementerian Pendidikan',
                'publication_year' => 2020,
                'category_slug' => 'pendidikan',
                'shelf_location' => 'E-01',
                'copies' => 5,
            ],
        ];

        foreach ($books as $bookData) {
            $category = Category::where('slug', $bookData['category_slug'])->first();
            $copiesCount = $bookData['copies'];
            unset($bookData['copies'], $bookData['category_slug']);

            $book = Book::updateOrCreate(
                ['isbn' => $bookData['isbn'] ?? uniqid('BK-')],
                array_merge($bookData, ['category_id' => $category?->id])
            );

            for ($i = 1; $i <= $copiesCount; $i++) {
                BookCopy::updateOrCreate(
                    ['barcode' => 'BK-'.str_pad($book->id * 100 + $i, 6, '0', STR_PAD_LEFT)],
                    [
                        'book_id' => $book->id,
                        'status' => BookCopyStatus::AVAILABLE,
                        'condition' => 'Baik',
                        'shelf_location' => $bookData['shelf_location'],
                    ]
                );
            }
        }
    }

    protected function seedTestLoans(): void
    {
        $student = Student::where('nipd', '12088')->first();
        $staff = User::where('email', 'petugas@skenda.com')->first();

        if (! $student || ! $staff) {
            return;
        }

        $copy = BookCopy::where('status', 'available')->first();
        if (! $copy) {
            return;
        }

        $loan = Loan::firstOrCreate(
            ['loan_number' => 'PJ-'.now()->format('Ymd').'-0001'],
            [
                'murid_id' => $student->id,
                'status' => LoanStatus::BORROWED,
                'requested_at' => now()->subDays(5),
                'approved_at' => now()->subDays(4),
                'borrowed_at' => now()->subDays(4),
                'due_at' => now()->addDays(3),
                'approved_by' => $staff->id,
            ]
        );

        LoanItem::firstOrCreate(
            ['loan_id' => $loan->id, 'book_copy_id' => $copy->id],
            [
                'due_at' => now()->addDays(3),
                'status' => LoanItemStatus::BORROWED,
            ]
        );

        $copy->update(['status' => BookCopyStatus::BORROWED]);
    }
}
