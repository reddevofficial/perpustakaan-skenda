<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookLabelController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array(Auth::user()?->role?->value ?? Auth::user()?->role, ['staff', 'super_admin'], true), 403);

        $ids = collect($request->input('ids', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        abort_if($ids->isEmpty(), 422, 'Pilih minimal satu eksemplar untuk dicetak.');

        $copies = BookCopy::query()
            ->with('book')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        abort_if($copies->isEmpty(), 404);

        return view('book-labels.index', [
            'copies' => $copies,
            'patterns' => $copies->mapWithKeys(fn (BookCopy $copy): array => [$copy->id => $this->pattern($copy->barcode)])->all(),
        ]);
    }

    private function pattern(string $value): string
    {
        $pattern = '101';

        foreach (str_split($value) as $character) {
            $pattern .= str_pad(decbin(ord($character)), 8, '0', STR_PAD_LEFT).'0';
        }

        return $pattern.'101';
    }
}
