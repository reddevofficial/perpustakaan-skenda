<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpustakaan SMKN 2 Banjarmasin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-gradient { background: linear-gradient(135deg, #312e81 0%, #4f46e5 50%, #6366f1 100%); }
    </style>
</head>
<body class="min-h-screen bg-zinc-50">
    <div class="hero-gradient min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <div class="text-center mb-10">
                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-3xl mx-auto mb-5 border border-white/30">
                    📚
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Perpustakaan</h1>
                <p class="text-indigo-200 text-lg">SMKN 2 Banjarmasin</p>
            </div>

            <div class="space-y-4">
                <a href="{{ route('filament.admin.pages.login') }}"
                   class="block w-full bg-white hover:bg-indigo-50 text-indigo-900 font-semibold py-4 px-6 rounded-xl text-center transition duration-200 shadow-lg hover:shadow-xl">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <div>
                            <div class="text-lg">Masuk sebagai Petugas</div>
                            <div class="text-xs text-indigo-500 font-normal">Admin, Guru, dan Staff Perpustakaan</div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('portal.login') }}"
                   class="block w-full bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white font-semibold py-4 px-6 rounded-xl text-center transition duration-200 border border-white/30">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <div>
                            <div class="text-lg">Masuk sebagai Siswa</div>
                            <div class="text-xs text-indigo-300 font-normal">E-Library, Cari & Pinjam Buku</div>
                        </div>
                    </div>
                </a>
            </div>

            <p class="text-center text-indigo-300/60 text-xs mt-8">
                &copy; {{ date('Y') }} SMKN 2 Banjarmasin
            </p>
        </div>
    </div>
</body>
</html>
