<div>
    <div class="mb-6">
        <a href="{{ route('portal.catalog') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Kembali ke Katalog</a>
    </div>

    <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
        <div class="md:flex">
            <div class="md:w-1/3 h-64 md:h-auto bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center">
                @if($book->cover)
                    <img src="{{ asset('storage/' . $book->cover) }}" alt="{{ $book->title }}" class="h-full w-full object-cover">
                @else
                    <span class="text-6xl text-indigo-300 font-bold">{{ substr($book->title, 0, 1) }}</span>
                @endif
            </div>
            <div class="md:w-2/3 p-6 md:p-8">
                <h1 class="text-2xl font-bold text-zinc-900 mb-4">{{ $book->title }}</h1>

                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div>
                        <span class="text-zinc-500">Penulis</span>
                        <p class="font-medium text-zinc-900">{{ $book->author }}</p>
                    </div>
                    <div>
                        <span class="text-zinc-500">Penerbit</span>
                        <p class="font-medium text-zinc-900">{{ $book->publisher }}</p>
                    </div>
                    <div>
                        <span class="text-zinc-500">Tahun Terbit</span>
                        <p class="font-medium text-zinc-900">{{ $book->publication_year }}</p>
                    </div>
                    <div>
                        <span class="text-zinc-500">ISBN</span>
                        <p class="font-medium text-zinc-900 font-mono">{{ $book->isbn ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-zinc-500">Kategori</span>
                        <p class="font-medium text-zinc-900">{{ $book->category->name ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-zinc-500">Lokasi Rak</span>
                        <p class="font-medium text-zinc-900">{{ $book->shelf_location ?? '-' }}</p>
                    </div>
                </div>

                @if($book->description)
                    <div class="mb-6">
                        <span class="text-zinc-500 text-sm">Deskripsi</span>
                        <p class="text-zinc-700 text-sm mt-1">{{ $book->description }}</p>
                    </div>
                @endif

                @php $available = $book->copies->where('status', 'available')->count(); @endphp
                <div class="flex items-center gap-4">
                    @if($available > 0)
                        <span class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium">{{ $available }} eksemplar tersedia</span>
                        @auth
                            @if(auth()->user()->isStudent())
                                <a href="#" wire:click.prevent="reserve()" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Reservasi</a>
                            @endif
                        @else
                            <a href="{{ route('portal.login') }}" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Masuk untuk Meminjam</a>
                        @endauth
                    @else
                        <span class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium">Tidak tersedia</span>
                        @auth
                            @if(auth()->user()->isStudent())
                                <a href="#" wire:click.prevent="reserve()" class="px-6 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 transition">Reservasi</a>
                            @endif
                        @else
                            <a href="{{ route('portal.login') }}" class="px-6 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700 transition">Masuk untuk Reservasi</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
