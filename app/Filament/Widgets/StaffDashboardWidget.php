<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Reservation;
use App\Models\Student;
use Filament\Widgets\Widget;

class StaffDashboardWidget extends Widget
{
    protected string $view = 'filament.widgets.staff-dashboard';

    protected static ?int $sort = 1;

    public $stats = [];

    public $urgentLoans = [];

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->isStaff();
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $pendingLoans = Loan::where('status', 'pending')->count();
        $activeLoans = Loan::where('status', 'borrowed')->count();
        $overdueLoans = Loan::where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'borrowed')
                    ->where('due_at', '<', now());
            })
            ->count();
        $totalBooks = Book::count();
        $activeStudents = Student::where('status', 'aktif')->count();
        $returnedToday = LoanItem::whereDate('returned_at', today())->count();
        $activeReservations = Reservation::whereIn('status', ['waiting', 'available'])->count();
        $unpaidFines = Fine::where('status', 'unpaid')->sum('amount');

        $this->stats = [
            ['label' => 'Peminjaman Pending', 'value' => $pendingLoans, 'description' => 'Menunggu persetujuan', 'icon' => 'heroicon-o-clock', 'color' => 'warning'],
            ['label' => 'Peminjaman Aktif', 'value' => $activeLoans, 'description' => 'Sedang dipinjam', 'icon' => 'heroicon-o-arrow-path', 'color' => 'info'],
            ['label' => 'Terlambat', 'value' => $overdueLoans, 'description' => 'Perlu ditindaklanjuti', 'icon' => 'heroicon-o-exclamation-triangle', 'color' => 'danger'],
            ['label' => 'Total Buku', 'value' => $totalBooks, 'description' => 'Judul buku', 'icon' => 'heroicon-o-book-open', 'color' => 'success'],
            ['label' => 'Siswa Aktif', 'value' => $activeStudents, 'description' => 'Terdaftar', 'icon' => 'heroicon-o-user-group', 'color' => 'gray'],
            ['label' => 'Pengembalian Hari Ini', 'value' => $returnedToday, 'description' => 'Buku dikembalikan', 'icon' => 'heroicon-o-arrow-uturn-left', 'color' => 'info'],
            ['label' => 'Reservasi Aktif', 'value' => $activeReservations, 'description' => 'Menunggu/tersedia', 'icon' => 'heroicon-o-bookmark', 'color' => 'warning'],
            ['label' => 'Denda Belum Dibayar', 'value' => 'Rp '.number_format($unpaidFines, 0, ',', '.'), 'description' => 'Total denda', 'icon' => 'heroicon-o-currency-dollar', 'color' => 'danger'],
        ];

        $this->urgentLoans = Loan::query()
            ->whereIn('status', ['pending', 'borrowed', 'overdue'])
            ->where(function ($q) {
                $q->where('status', 'pending')
                    ->orWhere('status', 'overdue')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'borrowed')
                            ->where('due_at', '<=', now()->endOfDay());
                    });
            })
            ->with(['student', 'items.bookCopy.book'])
            ->orderBy('due_at', 'asc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
