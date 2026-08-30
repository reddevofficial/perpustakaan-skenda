<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Perpustakaan SMKN 2' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-zinc-50 antialiased" x-data="{ mobileMenu: false }">

    <nav class="bg-white border-b border-zinc-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="{{ route('portal.catalog') }}" class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm">P</div>
                        <span class="font-semibold text-zinc-900 text-lg hidden sm:block">Perpustakaan SMKN 2</span>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('portal.loans') }}" class="px-3 py-2 text-sm text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition">Pinjaman Saya</a>
                        <a href="{{ route('portal.reservations') }}" class="px-3 py-2 text-sm text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 rounded-lg transition">Reservasi</a>
                        <a href="{{ route('portal.profile') }}" class="px-3 py-2 text-sm font-medium text-zinc-900 bg-zinc-100 rounded-lg">{{ auth()->user()->name }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-sm text-zinc-500 hover:text-red-600 rounded-lg transition">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('portal.login') }}" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-zinc-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-zinc-500">
            &copy; {{ date('Y') }} Perpustakaan SMKN 2 Banjarmasin
        </div>
    </footer>
</body>
</html>
