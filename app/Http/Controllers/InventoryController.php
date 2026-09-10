<?php

namespace App\Http\Controllers;

use App\Enums\BookCopyStatus;
use App\Models\BookCopy;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;

class InventoryController extends Controller
{
    public function index(): View
    {
        abort_unless(in_array(Auth::user()?->role?->value ?? Auth::user()?->role, ['staff', 'super_admin'], true), 403);

        return view('inventory.index');
    }

    public function update(InventoryService $inventoryService): RedirectResponse
    {
        abort_unless(in_array(Auth::user()?->role?->value ?? Auth::user()?->role, ['staff', 'super_admin'], true), 403);
        request()->validate([
            'barcode' => ['required', 'string', 'max:100'],
            'condition' => ['nullable', 'string', 'in:good,minor_damage,major_damage,lost'],
            'status' => ['nullable', 'string', 'in:available,reserved,borrowed,damaged,lost,inactive'],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $copy = BookCopy::query()->where('barcode', request('barcode'))->firstOrFail();

        try {
            $inventoryService->updateCopy(
                $copy,
                Auth::user(),
                request('condition'),
                request('status') ? BookCopyStatus::from(request('status')) : null,
                request('shelf_location'),
                request('notes'),
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Inventaris eksemplar berhasil diperbarui.');
    }
}
