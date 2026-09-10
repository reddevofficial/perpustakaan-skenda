<?php

namespace App\Services;

use App\Enums\BookCopyStatus;
use App\Models\BookCopy;
use App\Models\InventoryLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function updateCopy(
        BookCopy $copy,
        User $user,
        ?string $condition = null,
        ?BookCopyStatus $status = null,
        ?string $location = null,
        ?string $notes = null,
    ): BookCopy {
        return DB::transaction(function () use ($copy, $user, $condition, $status, $location, $notes): BookCopy {
            $copy = BookCopy::query()->with('book')->lockForUpdate()->findOrFail($copy->id);
            $oldStatus = $copy->status;
            $newStatus = $status ?? $oldStatus;

            if ($oldStatus === BookCopyStatus::BORROWED && $newStatus !== BookCopyStatus::BORROWED) {
                throw new RuntimeException('Eksemplar yang sedang dipinjam harus diproses melalui pengembalian.');
            }

            $wasAvailable = $oldStatus === BookCopyStatus::AVAILABLE;
            $isAvailable = $newStatus === BookCopyStatus::AVAILABLE;

            $copy->update(array_filter([
                'condition' => $condition,
                'status' => $newStatus,
                'shelf_location' => $location,
                'notes' => $notes,
            ], static fn (mixed $value): bool => $value !== null));

            if ($wasAvailable && ! $isAvailable) {
                $copy->book()->decrement('available_stock');
            } elseif (! $wasAvailable && $isAvailable) {
                $copy->book()->increment('available_stock');
            }

            InventoryLog::query()->create([
                'book_copy_id' => $copy->id,
                'user_id' => $user->id,
                'condition' => $copy->condition,
                'status' => $copy->status->value,
                'location' => $copy->shelf_location,
                'notes' => $notes ?? 'Pembaruan inventaris',
            ]);

            return $copy->fresh(['book']);
        });
    }

    public function recordOpname(BookCopy $copy, User $user, bool $found, ?string $notes = null): InventoryLog
    {
        $status = $found ? $copy->status : BookCopyStatus::LOST;
        $condition = $found ? $copy->condition : 'lost';

        $this->updateCopy($copy, $user, $condition, $status, $copy->shelf_location, $notes ?? ($found ? 'Ditemukan saat stock opname' : 'Tidak ditemukan saat stock opname'));

        return $copy->inventoryLogs()->latest()->firstOrFail();
    }
}
