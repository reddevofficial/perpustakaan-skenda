<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 mb-2">Katalog Buku</h1>
        <p class="text-zinc-500">Temukan buku yang ingin Anda baca</p>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Cari judul, penulis, ISBN..."
                   class="w-full px-4 py-2.5 bg-white border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
        </div>
        <div>
            <select wire:model.live="categoryId" class="px-4 py-2.5 bg-white border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->books_count }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="availability" class="px-4 py-2.5 bg-white border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                <option value="">Semua Status</option>
                <option value="available">Tersedia</option>
                <option value="unavailable">Tidak Tersedia</option>
            </select>
        </div>
    </div>

    @if($books->isEmpty())
        <div class="text-center py-16">
            <p class="text-zinc-400 text-lg">Tidak ditemukan buku.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($books as $book)
                <a href="{{ route('portal.book', $book->id) }}" class="bg-white rounded-xl border border-zinc-200 overflow-hidden hover:shadow-lg transition-shadow group">
                    <div class="h-48 bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center">
                        @if($book->cover)
                            <img src="{{ asset('storage/' . $book->cover) }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-4xl text-indigo-300 font-bold">{{ substr($book->title, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-zinc-900 group-hover:text-indigo-600 transition line-clamp-2 mb-1">{{ $book->title }}</h3>
                        <p class="text-sm text-zinc-500 mb-2">{{ $book->author }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-xs px-2 py-1 bg-zinc-100 text-zinc-600 rounded-md">{{ $book->category->name ?? '-' }}</span>
                            @php $available = $book->copies->where('status', 'available')->count(); @endphp
                            @if($available > 0)
                                <span class="text-xs px-2 py-1 bg-emerald-100 text-emerald-700 rounded-md font-medium">{{ $available }} tersedia</span>
                            @else
                                <span class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-md font-medium">Tidak tersedia</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $books->links() }}
        </div>
    @endif
</div>
