<?php

namespace App\Filament\Widgets;

use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LibraryStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        if ($user?->role === UserRole::STUDENT) {
            $student = $user->student;
            $activeLoans = $student?->loans()->whereIn('status', [LoanStatus::BORROWED->value, LoanStatus::OVERDUE->value])->count() ?? 0;
            $overdue = $student?->loans()->where('status', LoanStatus::OVERDUE->value)->count() ?? 0;
            $fines = $student?->fines()->where('status', 'unpaid')->sum('total_amount') ?? 0;
            $reservations = $student?->reservations()->whereIn('status', [ReservationStatus::WAITING->value, ReservationStatus::AVAILABLE->value])->count() ?? 0;

            return [
                Stat::make('Sedang dipinjam', $activeLoans)->description('Buku aktif Anda')->color('primary'),
                Stat::make('Terlambat', $overdue)->description('Perlu dikembalikan')->color('danger'),
                Stat::make('Denda belum dibayar', 'Rp '.number_format($fines, 0, ',', '.'))->color('warning'),
                Stat::make('Reservasi aktif', $reservations)->color('info'),
            ];
        }

        $pending = Loan::query()->where('status', LoanStatus::PENDING->value)->count();
        $borrowed = Loan::query()->whereIn('status', [LoanStatus::BORROWED->value, LoanStatus::OVERDUE->value])->count();
        $overdue = Loan::query()->where('status', LoanStatus::OVERDUE->value)->count();
        $unpaidFines = Fine::query()->where('status', 'unpaid')->sum('total_amount');

        return [
            Stat::make('Total buku', Book::query()->count())->description(BookCopy::query()->count().' eksemplar')->color('primary'),
            Stat::make('Total anggota', Student::query()->count())->color('info'),
            Stat::make('Menunggu persetujuan', $pending)->color('warning'),
            Stat::make('Sedang dipinjam', $borrowed)->color('success'),
            Stat::make('Terlambat', $overdue)->color('danger'),
            Stat::make('Denda belum dibayar', 'Rp '.number_format($unpaidFines, 0, ',', '.'))->color('warning'),
            Stat::make('Reservasi aktif', Reservation::query()->whereIn('status', [ReservationStatus::WAITING->value, ReservationStatus::AVAILABLE->value])->count())->color('info'),
        ];
    }

    protected function getColumns(): int|array
    {
        return ['md' => 2, 'xl' => 4];
    }
}
