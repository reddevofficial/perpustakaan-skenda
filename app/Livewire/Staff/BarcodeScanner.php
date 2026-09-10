<?php

namespace App\Livewire\Staff;

use App\Enums\UserRole;
use App\Models\BookCopy;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BarcodeScanner extends Component
{
    public string $barcode = '';
    public ?BookCopy $copy = null;
    public ?string $message = null;

    public function mount(): void
    {
        abort_unless(in_array(Auth::user()?->role, [UserRole::STAFF, UserRole::SUPER_ADMIN], true), 403);
    }

    public function lookup(): void
    {
        $this->validate(['barcode' => ['required', 'string', 'max:100']]);
        $this->copy = BookCopy::query()->with('book.category')->where('barcode', $this->barcode)->first();
        $this->message = $this->copy ? null : 'Barcode tidak ditemukan.';
    }

    public function clear(): void
    {
        $this->reset(['barcode', 'copy', 'message']);
    }

    public function render(): View
    {
        return view('livewire.staff.barcode-scanner');
    }
}
