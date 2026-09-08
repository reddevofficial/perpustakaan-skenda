<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Buku</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-3xl px-6 py-10">
        <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Operasional petugas</p>
            <h1 class="mt-2 text-3xl font-bold">Pembaruan Inventaris</h1>
            <p class="mt-2 text-sm text-slate-300">Scan barcode eksemplar lalu perbarui kondisi dan lokasi fisiknya.</p>
        </div>
        @if (session('success')) <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="mt-6 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div> @endif
        <form method="POST" action="{{ route('inventory.update') }}" class="mt-6 grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2"><label for="barcode" class="text-sm font-semibold">Barcode</label><input id="barcode" name="barcode" required autofocus autocomplete="off" class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3" placeholder="Scan barcode eksemplar"></div>
            <div><label for="condition" class="text-sm font-semibold">Kondisi</label><select id="condition" name="condition" class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3"><option value="good">Baik</option><option value="minor_damage">Rusak ringan</option><option value="major_damage">Rusak berat</option><option value="lost">Hilang</option></select></div>
            <div><label for="status" class="text-sm font-semibold">Status</label><select id="status" name="status" class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3"><option value="available">Tersedia</option><option value="reserved">Direservasi</option><option value="damaged">Rusak</option><option value="lost">Hilang</option><option value="inactive">Tidak aktif</option></select></div>
            <div><label for="shelf_location" class="text-sm font-semibold">Lokasi rak</label><input id="shelf_location" name="shelf_location" class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3"></div>
            <div><label for="notes" class="text-sm font-semibold">Catatan</label><input id="notes" name="notes" class="mt-2 w-full rounded-xl border-slate-200 px-4 py-3"></div>
            <button class="sm:col-span-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700">Simpan pemeriksaan</button>
        </form>
    </main>
</body>
</html>
