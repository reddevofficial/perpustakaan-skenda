<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perpustakaan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style media="print">.print-hidden { display: none !important; } body { background: white; } .report-table { font-size: 11px; }</style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-7xl px-6 py-10">
        <header class="flex flex-wrap items-end justify-between gap-5 print-hidden">
            <div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Administrasi perpustakaan</p><h1 class="mt-1 text-3xl font-bold">Laporan</h1></div>
            <div class="flex gap-3"><a href="{{ route('reports.export', request()->query()) }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Export CSV</a><button onclick="window.print()" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Print / PDF</button></div>
        </header>
        <form method="GET" class="mt-8 grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm print-hidden md:grid-cols-3">
            <select name="type" class="rounded-xl border-slate-200 px-3 py-2.5"><option value="loans" @selected($type === 'loans')>Peminjaman</option><option value="fines" @selected($type === 'fines')>Denda</option><option value="reservations" @selected($type === 'reservations')>Reservasi</option><option value="inventory" @selected($type === 'inventory')>Inventaris</option><option value="books" @selected($type === 'books')>Buku</option></select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border-slate-200 px-3 py-2.5" aria-label="Dari tanggal">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border-slate-200 px-3 py-2.5" aria-label="Sampai tanggal">
            <select name="category_id" class="rounded-xl border-slate-200 px-3 py-2.5"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select>
            <select name="class" class="rounded-xl border-slate-200 px-3 py-2.5"><option value="">Semua kelas</option>@foreach ($classes as $class)<option value="{{ $class }}" @selected(($filters['class'] ?? '') === $class)>{{ $class }}</option>@endforeach</select>
            <input name="status" value="{{ $filters['status'] ?? '' }}" placeholder="Status, mis. returned" class="rounded-xl border-slate-200 px-3 py-2.5">
            <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 md:col-span-3">Terapkan filter</button>
        </form>
        <section class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-bold">{{ ucfirst($type) }}</h2><p class="mt-1 text-sm text-slate-500">{{ $records->total() }} data ditemukan</p></div>
            <div class="overflow-x-auto"><table class="report-table w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr>@foreach ($headers as $header)<th class="whitespace-nowrap px-5 py-3">{{ $header }}</th>@endforeach</tr></thead><tbody class="divide-y divide-slate-100">@foreach ($records as $record)<tr>@foreach (app(\App\Services\ReportService::class)->rows($type, [$record])[0] ?? [] as $value)<td class="whitespace-nowrap px-5 py-3">{{ $value ?? '-' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headers) }}" class="px-5 py-12 text-center text-slate-500">Belum ada data untuk filter ini.</td></tr>@endforeach</tbody></table></div>
            <div class="border-t border-slate-100 px-5 py-4 print-hidden">{{ $records->links() }}</div>
        </section>
    </main>
</body>
</html>
