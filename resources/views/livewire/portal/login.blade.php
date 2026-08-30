<div>
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">P</div>
            <h1 class="text-2xl font-bold text-zinc-900">Masuk</h1>
            <p class="text-sm text-zinc-500 mt-1">Gunakan email atau NIPD untuk masuk</p>
        </div>

        <div class="bg-white rounded-xl border border-zinc-200 p-6">
            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="login">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Email atau NIPD</label>
                    <input wire:model="identity" type="text" required
                           class="w-full px-4 py-2.5 bg-white border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                           placeholder="email@contoh.com atau NIPD">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Password</label>
                    <input wire:model="password" type="password" required
                           class="w-full px-4 py-2.5 bg-white border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm text-zinc-600">
                        <input wire:model="remember" type="checkbox" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
