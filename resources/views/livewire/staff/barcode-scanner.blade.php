<div class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-3xl px-6 py-10">
        <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-300">Operasional petugas</p>
            <h1 class="mt-2 text-3xl font-bold">Scan Barcode Buku</h1>
            <p class="mt-2 text-sm text-slate-300">Gunakan scanner USB seperti keyboard atau input barcode secara manual.</p>
            <form wire:submit="lookup" class="mt-6 flex gap-3">
                <label for="barcode" class="sr-only">Barcode buku</label>
                <input id="barcode" wire:model="barcode" type="text" autofocus autocomplete="off" placeholder="Arahkan fokus lalu scan barcode..." class="min-w-0 flex-1 rounded-xl border-0 px-4 py-3 text-slate-900 outline-none ring-2 ring-transparent focus:ring-sky-400">
                <button type="submit" class="rounded-xl bg-sky-400 px-5 py-3 text-sm font-bold text-slate-950 hover:bg-sky-300">Cari</button>
            </form>
            @error('barcode') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        @if ($message)
            <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">{{ $message }}</div>
        @endif

        @if ($copy)
            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Hasil barcode</p>
                    <h2 class="mt-1 text-2xl font-bold">{{ $copy->book->title }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $copy->book->author }}</p>
                </div>
                <dl class="grid gap-5 px-6 py-6 sm:grid-cols-2">
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Barcode</dt><dd class="mt-1 font-semibold">{{ $copy->barcode }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">ID buku</dt><dd class="mt-1 font-semibold">{{ $copy->book->book_code }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Status</dt><dd class="mt-1 font-semibold">{{ $copy->status->name }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Lokasi rak</dt><dd class="mt-1 font-semibold">{{ $copy->shelf_location ?: $copy->book->shelf_location ?: '-' }}</dd></div>
                </dl>
                <div class="flex gap-3 border-t border-slate-100 px-6 py-4">
                    <button type="button" wire:click="clear" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Scan berikutnya</button>
                    <a href="{{ route('portal.book', $copy->book) }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Lihat detail buku</a>
                </div>
            </section>
        @endif
    </main>
</div>
