<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 mb-2">Notifikasi</h1>
    </div>

    @if($notifications->isEmpty())
        <div class="bg-white rounded-xl border border-zinc-200 p-8 text-center">
            <p class="text-zinc-400">Tidak ada notifikasi.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $notification)
                <div class="bg-white rounded-xl border border-zinc-200 p-4 {{ $notification->read_at ? 'opacity-60' : '' }}">
                    <p class="text-sm text-zinc-900">{{ $notification->data['message'] ?? 'Notifikasi' }}</p>
                    <p class="text-xs text-zinc-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
