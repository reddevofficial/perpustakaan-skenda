<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 mb-2">Pinjaman Saya</h1>
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

    @if($loans->isEmpty())
        <div class="bg-white rounded-xl border border-zinc-200 p-8 text-center">
            <p class="text-zinc-400">Anda belum memiliki pinjaman.</p>
            <a href="{{ route('portal.catalog') }}" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Lihat Katalog</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($loans as $loan)
                <div class="bg-white rounded-xl border border-zinc-200 p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="font-mono text-sm text-zinc-500">{{ $loan->loan_number }}</p>
                            <p class="text-xs text-zinc-400">{{ $loan->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(in_array($loan->id, $extendableLoanIds))
                                <a href="{{ route('portal.request-extension', $loan->id) }}" class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-xs font-medium hover:bg-indigo-700 transition">
                                    Ajukan Perpanjangan
                                </a>
                            @endif
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                {{ match($loan->status->value) {
                                    'borrowed' => 'bg-blue-100 text-blue-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'returned' => 'bg-emerald-100 text-emerald-700',
                                    'overdue' => 'bg-red-100 text-red-700',
                                    default => 'bg-zinc-100 text-zinc-700',
                                } }}">
                                {{ $loan->status->label() }}
                            </span>
                        </div>
                    </div>

                    @if($loan->extensions->isNotEmpty())
                        <div class="mb-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-xs font-medium text-amber-800 mb-1">Status Perpanjangan:</p>
                            @foreach($loan->extensions as $extension)
                                <div class="flex items-center gap-2 text-xs text-amber-700">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ match($extension->status->value) {
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'approved' => 'bg-emerald-100 text-emerald-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            default => 'bg-zinc-100 text-zinc-700',
                                        } }}">
                                        {{ $extension->status->label() }}
                                    </span>
                                    <span>Jatuh tempo baru: {{ $extension->new_due_at->format('d M Y') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="space-y-2">
                        @foreach($loan->items as $item)
                            <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-sm">
                                    {{ substr($item->bookCopy->book->title ?? 'B', 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 truncate">{{ $item->bookCopy->book->title ?? '-' }}</p>
                                    <p class="text-xs text-zinc-500 font-mono">{{ $item->bookCopy->barcode }}</p>
                                </div>
                                @if($loan->status->value === 'borrowed')
                                    <div class="text-right">
                                        <p class="text-xs text-zinc-500">Jatuh tempo</p>
                                        <p class="text-sm font-medium {{ $item->due_at->isPast() ? 'text-red-600' : 'text-zinc-900' }}">{{ $item->due_at->format('d M Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
