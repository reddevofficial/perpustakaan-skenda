<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-5xl px-6 py-10">
        <div class="flex items-center justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Perpustakaan Skenda</p><h1 class="mt-1 text-3xl font-bold">Peminjaman Saya</h1></div>
            <a href="{{ route('portal.catalog') }}" class="text-sm font-semibold text-indigo-600">Katalog</a>
        </div>
        @if (session('success')) <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="mt-6 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div> @endif
        <div class="mt-8 space-y-4">
            @forelse ($loans as $loan)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $loan->loan_code }}</p><h2 class="mt-1 text-lg font-bold">{{ $loan->items->pluck('bookCopy.book.title')->join(', ') }}</h2></div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $loan->status->name }}</span>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3"><div><dt class="text-slate-400">Diajukan</dt><dd class="font-semibold">{{ $loan->requested_at?->format('d M Y H:i') ?: '-' }}</dd></div><div><dt class="text-slate-400">Jatuh tempo</dt><dd class="font-semibold">{{ $loan->due_at?->format('d M Y') ?: '-' }}</dd></div><div><dt class="text-slate-400">Denda</dt><dd class="font-semibold">Rp {{ number_format($loan->fine?->total_amount ?? 0, 0, ',', '.') }}</dd></div></dl>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-sm text-slate-500">Belum ada riwayat peminjaman.</div>
            @endforelse
        </div>
        <div class="mt-6">{{ $loans->links() }}</div>
    </main>
</body>
</html>