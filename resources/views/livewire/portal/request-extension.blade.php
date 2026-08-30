<div>
    <div class="mb-8">
        <a href="{{ route('portal.loans') }}" class="text-sm text-indigo-600 hover:text-indigo-700 mb-2 inline-block">&larr; Kembali ke Pinjaman Saya</a>
        <h1 class="text-2xl font-bold text-zinc-900 mb-2">Ajukan Perpanjangan</h1>
        <p class="text-sm text-zinc-500">Ajukan perpanjangan waktu peminjaman buku.</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-zinc-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-zinc-900 mb-4">Detail Peminjaman</h2>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <p class="text-xs text-zinc-500 mb-1">No. Peminjaman</p>
                <p class="text-sm font-medium text-zinc-900 font-mono">{{ $loan->loan_number }}</p>
            </div>
            <div>
                <p class="text-xs text-zinc-500 mb-1">Jatuh Tempo</p>
                <p class="text-sm font-medium text-zinc-900">{{ $loan->due_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="space-y-2">
            <p class="text-xs text-zinc-500 mb-1">Buku yang dipinjam:</p>
            @foreach($loan->items as $item)
                <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-sm">
                        {{ substr($item->bookCopy->book->title ?? 'B', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-zinc-900 truncate">{{ $item->bookCopy->book->title ?? '-' }}</p>
                        <p class="text-xs text-zinc-500 font-mono">{{ $item->bookCopy->barcode }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl border border-zinc-200 p-6">
        <h2 class="text-lg font-semibold text-zinc-900 mb-4">Formulir Perpanjangan</h2>

        <form wire:submit="submit">
            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-zinc-700 mb-1">Alasan Perpanjangan <span class="text-red-500">*</span></label>
                <textarea
                    wire:model="reason"
                    id="reason"
                    rows="4"
                    class="w-full rounded-lg border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none resize-none"
                    placeholder="Jelaskan alasan Anda membutuhkan perpanjangan..."
                ></textarea>
                @error('reason')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="submit">Ajukan Perpanjangan</span>
                    <span wire:loading wire:target="submit">Mengirim...</span>
                </button>
                <a href="{{ route('portal.loans') }}" class="px-5 py-2.5 bg-zinc-100 text-zinc-700 rounded-lg text-sm font-medium hover:bg-zinc-200 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
