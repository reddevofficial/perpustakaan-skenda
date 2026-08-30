<x-filament-panels::page>
    <x-filament::section heading="Scan Barcode Buku">
        <div class="flex gap-3">
            <div class="flex-1">
                <input type="text" wire:model.live="barcode" placeholder="Masukkan atau scan barcode buku..."
                       class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition" autofocus />
            </div>
            <x-filament::button wire:click="lookupBarcode" color="primary" icon="heroicon-o-magnifying-glass">
                Cari
            </x-filament::button>
        </div>
    </x-filament::section>

    @if ($this->foundLoan)
        <x-filament::section heading="Detail Peminjaman" class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">No. Peminjaman</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $this->foundLoan->loan_number }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Siswa</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $this->foundLoan->student->nama }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Buku</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $this->foundItem->bookCopy->book->title }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Barcode</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $this->foundItem->bookCopy->barcode }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Tanggal Pinjam</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $this->foundLoan->borrowed_at?->format('d M Y H:i') ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Jatuh Tempo</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $this->foundItem->due_at->format('d M Y') }}</p>
                </div>
                @if ($this->foundItem->due_at->isPast())
                    <div>
                        <label class="text-sm font-medium text-gray-500">Keterlambatan</label>
                        <p class="mt-1 text-sm font-semibold text-danger-600">
                            {{ $this->foundItem->due_at->diffInDays(now()) }} hari
                        </p>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <x-filament::button wire:click="processReturn" color="success" icon="heroicon-o-check">
                    Konfirmasi Pengembalian
                </x-filament::button>
            </div>
        </x-filament::section>
    @elseif ($this->barcode)
        <x-filament::section heading="Hasil Pencarian" class="mt-6">
            <div class="text-center py-8 text-gray-500">
                <x-heroicon-o-magnifying-glass class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <p>Tidak ditemukan peminjaman aktif untuk barcode: <strong>{{ $this->barcode }}</strong></p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
