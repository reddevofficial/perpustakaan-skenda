<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Fine;
use App\Models\InventoryLog;
use App\Models\Loan;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    public function loans(array $filters): Builder
    {
        return Loan::query()
            ->with(['student.user', 'items.bookCopy.book.category', 'fine'])
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['class'] ?? null, fn (Builder $query, string $class) => $query->whereHas('student', fn (Builder $student) => $student->where('class', $class)))
            ->when($filters['category_id'] ?? null, fn (Builder $query, string $categoryId) => $query->whereHas('items.bookCopy.book', fn (Builder $book) => $book->where('category_id', $categoryId)))
            ->latest();
    }

    public function fines(array $filters): Builder
    {
        return Fine::query()
            ->with(['student.user', 'loan'])
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['class'] ?? null, fn (Builder $query, string $class) => $query->whereHas('student', fn (Builder $student) => $student->where('class', $class)))
            ->latest();
    }

    public function reservations(array $filters): Builder
    {
        return Reservation::query()
            ->with(['student.user', 'book.category'])
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('reserved_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('reserved_at', '<=', $to))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['class'] ?? null, fn (Builder $query, string $class) => $query->whereHas('student', fn (Builder $student) => $student->where('class', $class)))
            ->when($filters['category_id'] ?? null, fn (Builder $query, string $categoryId) => $query->whereHas('book', fn (Builder $book) => $book->where('category_id', $categoryId)))
            ->latest('reserved_at');
    }

    public function inventory(array $filters): Builder
    {
        return InventoryLog::query()
            ->with(['bookCopy.book', 'user'])
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest();
    }

    public function books(array $filters): Builder
    {
        return Book::query()
            ->with('category')
            ->withCount('copies')
            ->when($filters['category_id'] ?? null, fn (Builder $query, string $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('title');
    }

    public function rows(string $type, iterable $records): array
    {
        return match ($type) {
            'loans' => collect($records)->map(fn (Loan $loan): array => [
                $loan->loan_code,
                $loan->student?->student_number,
                $loan->student?->user?->name,
                $loan->items->pluck('bookCopy.book.title')->join(', '),
                $loan->status?->value,
                $loan->requested_at?->format('Y-m-d H:i'),
                $loan->due_at?->format('Y-m-d'),
                $loan->returned_at?->format('Y-m-d H:i'),
            ])->all(),
            'fines' => collect($records)->map(fn (Fine $fine): array => [
                $fine->loan?->loan_code,
                $fine->student?->student_number,
                $fine->student?->user?->name,
                $fine->overdue_days,
                $fine->daily_rate,
                $fine->total_amount,
                $fine->status?->value,
                $fine->paid_at?->format('Y-m-d H:i'),
            ])->all(),
            'reservations' => collect($records)->map(fn (Reservation $reservation): array => [
                $reservation->reservation_code,
                $reservation->student?->student_number,
                $reservation->student?->user?->name,
                $reservation->book?->title,
                $reservation->queue_number,
                $reservation->status?->value,
                $reservation->reserved_at?->format('Y-m-d H:i'),
                $reservation->expires_at?->format('Y-m-d H:i'),
            ])->all(),
            'inventory' => collect($records)->map(fn (InventoryLog $log): array => [
                $log->bookCopy?->barcode,
                $log->bookCopy?->book?->title,
                $log->condition,
                $log->status,
                $log->location,
                $log->user?->name,
                $log->created_at?->format('Y-m-d H:i'),
            ])->all(),
            'books' => collect($records)->map(fn (Book $book): array => [
                $book->book_code,
                $book->title,
                $book->author,
                $book->category?->name,
                $book->stock,
                $book->available_stock,
                $book->status,
            ])->all(),
            default => [],
        };
    }

    public function headers(string $type): array
    {
        return match ($type) {
            'loans' => ['Kode', 'NIS', 'Anggota', 'Buku', 'Status', 'Diajukan', 'Jatuh Tempo', 'Dikembalikan'],
            'fines' => ['Kode Peminjaman', 'NIS', 'Anggota', 'Hari Terlambat', 'Tarif/Hari', 'Total', 'Status', 'Dibayar'],
            'reservations' => ['Kode', 'NIS', 'Anggota', 'Buku', 'Antrean', 'Status', 'Dipesan', 'Kadaluarsa'],
            'inventory' => ['Barcode', 'Buku', 'Kondisi', 'Status', 'Lokasi', 'Petugas', 'Diperiksa'],
            'books' => ['ID Buku', 'Judul', 'Penulis', 'Kategori', 'Stok', 'Tersedia', 'Status'],
            default => [],
        };
    }
}
