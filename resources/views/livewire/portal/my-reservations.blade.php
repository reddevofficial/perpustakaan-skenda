<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 mb-2">Reservasi Saya</h1>
    </div>

    @if($reservations->isEmpty())
        <div class="bg-white rounded-xl border border-zinc-200 p-8 text-center">
            <p class="text-zinc-400">Anda belum memiliki reservasi.</p>
            <a href="{{ route('portal.catalog') }}" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Lihat Katalog</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($reservations as $reservation)
                <div class="bg-white rounded-xl border border-zinc-200 p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-mono text-sm text-zinc-500">{{ $reservation->reservation_number }}</p>
                            <p class="font-medium text-zinc-900 mt-1">{{ $reservation->book->title ?? '-' }}</p>
                            <p class="text-sm text-zinc-500">Antrean #{{ $reservation->queue_position }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            {{ match($reservation->status->value) {
                                'waiting' => 'bg-amber-100 text-amber-700',
                                'available' => 'bg-emerald-100 text-emerald-700',
                                'fulfilled' => 'bg-blue-100 text-blue-700',
                                'expired' => 'bg-zinc-100 text-zinc-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default => 'bg-zinc-100 text-zinc-700',
                            } }}">
                            {{ $reservation->status->label() }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
