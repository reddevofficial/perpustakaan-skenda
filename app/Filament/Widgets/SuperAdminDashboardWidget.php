<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Student;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminDashboardWidget extends StatsOverviewWidget
{
    protected static ?string $navigationGroup = 'Perpustakaan';

    protected static ?int $navigationSort = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->isStaff();
    }

    protected function getStats(): array
    {
        $totalBooks = Book::count();
        $totalCopies = BookCopy::count();
        $totalStudents = Student::count();
        $totalStaff = User::where('role_id', '!=', 7)->count();
        $loansThisMonth = Loan::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $overdueLoans = Loan::where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'borrowed')
                    ->where('due_at', '<', now());
            })
            ->count();
        $unpaidFines = Fine::where('status', 'unpaid')->sum('amount');

        return [
            Stat::make('Total Buku', $totalBooks)
                ->description('Judul buku')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('success'),
            Stat::make('Total Eksemplar', $totalCopies)
                ->description('Semua eksemplar')
                ->descriptionIcon('heroicon-o-circle-stack')
                ->color('info'),
            Stat::make('Total Siswa', $totalStudents)
                ->description('Terdaftar')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('gray'),
            Stat::make('Total Petugas', $totalStaff)
                ->description('Akun petugas')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Peminjaman Bulan Ini', $loansThisMonth)
                ->description('Bulan '.now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),
            Stat::make('Buku Terlambat', $overdueLoans)
                ->description('Perlu ditindaklanjuti')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger'),
            Stat::make('Denda Belum Dibayar', 'Rp '.number_format($unpaidFines, 0, ',', '.'))
                ->description('Total denda')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('danger'),
        ];
    }
}
