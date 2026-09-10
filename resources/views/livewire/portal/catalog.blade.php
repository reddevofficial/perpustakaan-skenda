<div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Perpustakaan Skenda</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight">Katalog Buku</h1>
            </div>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600">Dashboard</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
        <section class="rounded-2xl bg-slate-900 p-6 text-white shadow-xl">
            <div class="max-w-2xl">
                <p class="text-sm font-medium text-indigo-300">Temukan bacaan berikutnya</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight">Cari koleksi perpustakaan sekolah</h2>
                <p class="mt-3 text-sm leading-6 text-slate-300">Telusuri judul, penulis, ISBN, ID buku, atau barcode eksemplar.</p>
            </div>
            <div class="mt-6">
                <label for="catalog-search" class="sr-only">Cari buku</label>
                <input id="catalog-search" wire:model.live.debounce.350ms="search" type="search" placeholder="Cari judul, penulis, ISBN, atau barcode..." class="w-full rounded-xl border-0 bg-white px-4 py-3 text-slate-900 outline-none ring-2 ring-transparent focus:ring-indigo-400">
            </div>
        </section>

        <section class="mt-6 grid gap-3 md:grid-cols-4">
            <select wire:model.live="categoryId" class="rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="year" class="rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm">
                <option value="">Semua tahun</option>
                @foreach ($years as $bookYear)
                    <option value="{{ $bookYear }}">{{ $bookYear }}</option>
                @endforeach
            </select>
            <select wire:model.live="availability" class="rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm">
                <option value="">Semua ketersediaan</option>
                <option value="available">Tersedia</option>
                <option value="unavailable">Stok habis</option>
            </select>
            <div class="flex gap-2">
                <select wire:model.live="sortBy" class="min-w-0 flex-1 rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm">
                    <option value="title">Judul</option>
                    <option value="author">Penulis</option>
                    <option value="publication_year">Tahun terbit</option>
                    <option value="available_stock">Stok tersedia</option>
                </select>
                <button type="button" wire:click="toggleSortDirection" class="rounded-xl bg-white px-3 text-sm font-semibold text-slate-600 shadow-sm" title="Ubah urutan">{{ $sortDirection === 'asc' ? 'A-Z' : 'Z-A' }}</button>
            </div>
        </section>

        <div class="mt-8 flex items-center justify-between">
            <p class="text-sm text-slate-500">{{ $books->total() }} buku ditemukan</p>
            @if ($search || $categoryId || $availability || $year)
                <button type="button" wire:click="clearFilters" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Reset filter</button>
            @endif
        </div>

        @if ($books->count())
            <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($books as $book)
                    <article wire:key="book-{{ $book->id }}" class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-48 items-center justify-center bg-slate-100">
                            @if ($book->cover)
                                <img src="{{ Storage::disk('public')->url($book->cover) }}" alt="Cover {{ $book->title }}" class="h-full w-full object-cover">
                            @else
                                <span class="text-sm font-semibold text-slate-400">Tidak ada cover</span>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $book->category?->name ?? 'Umum' }}</span>
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $book->available_stock > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $book->available_stock > 0 ? 'Tersedia' : 'Habis' }}</span>
                            </div>
                            <h3 class="mt-3 line-clamp-2 text-lg font-bold leading-6">{{ $book->title }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $book->author }}</p>
                            <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                                <span>{{ $book->publication_year ?: 'Tahun tidak tersedia' }}</span>
                                <span>{{ $book->available_stock }} tersedia</span>
                            </div>
                            <a href="{{ route('portal.book', $book) }}" class="mt-5 inline-flex justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Lihat detail</a>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8">{{ $books->links() }}</div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <h3 class="text-lg font-bold">Buku tidak ditemukan</h3>
                <p class="mt-2 text-sm text-slate-500">Coba ubah kata kunci atau filter pencarian.</p>
            </div>
        @endif
    </main>
</div>
