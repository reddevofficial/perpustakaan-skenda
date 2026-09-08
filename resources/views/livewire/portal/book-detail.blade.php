<div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
            <a href="{{ route('portal.catalog') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">&larr; Kembali ke katalog</a>
            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Detail buku</span>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10">
        <div class="grid gap-10 lg:grid-cols-[280px_1fr]">
            <div class="flex h-[380px] items-center justify-center overflow-hidden rounded-2xl bg-slate-200 shadow-lg">
                @if ($book->cover)
                    <img src="{{ Storage::disk('public')->url($book->cover) }}" alt="Cover {{ $book->title }}" class="h-full w-full object-cover">
                @else
                    <span class="text-sm font-semibold text-slate-400">Tidak ada cover</span>
                @endif
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $book->category?->name ?? 'Umum' }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $book->available_stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $book->available_stock > 0 ? 'Tersedia' : 'Stok habis' }}</span>
                </div>
                <h1 class="mt-4 text-4xl font-bold tracking-tight">{{ $book->title }}</h1>
                <p class="mt-3 text-lg text-slate-600">{{ $book->author }}</p>
                <dl class="mt-8 grid gap-4 border-y border-slate-200 py-6 sm:grid-cols-2">
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">ID Buku</dt><dd class="mt-1 font-semibold">{{ $book->book_code }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">ISBN</dt><dd class="mt-1 font-semibold">{{ $book->isbn ?: '-' }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Penerbit</dt><dd class="mt-1 font-semibold">{{ $book->publisher ?: '-' }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Tahun terbit</dt><dd class="mt-1 font-semibold">{{ $book->publication_year ?: '-' }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Lokasi rak</dt><dd class="mt-1 font-semibold">{{ $book->shelf_location ?: '-' }}</dd></div>
                    <div><dt class="text-xs uppercase tracking-wide text-slate-400">Ketersediaan</dt><dd class="mt-1 font-semibold">{{ $book->available_stock }} dari {{ $book->stock }} eksemplar</dd></div>
                </dl>
                <div class="prose prose-slate max-w-none text-sm leading-7">
                    <p>{{ $book->description ?: 'Belum ada deskripsi untuk buku ini.' }}</p>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        @if ($book->available_stock > 0)
                            @if (auth()->user()->student)
                                <form method="POST" action="{{ route('loans.store', $book) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Ajukan peminjaman</button>
                                </form>
                            @else
                                <a href="{{ route('dashboard.redirect') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Buka dashboard</a>
                            @endif
                        @else
                            @if (auth()->user()->student)
                                <form method="POST" action="{{ route('reservations.store', $book) }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-600">Reservasi buku</button>
                                </form>
                            @else
                                <button type="button" disabled class="cursor-not-allowed rounded-xl bg-slate-200 px-5 py-3 text-sm font-semibold text-slate-500">Reservasi untuk siswa</button>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('filament.student.auth.login') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">Login untuk meminjam</a>
                    @endauth
                </div>
            </div>
        </div>
    </main>
</div>
