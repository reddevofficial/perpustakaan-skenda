<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-zinc-900 mb-2">Profil Saya</h1>
    </div>

    @if($student)
        <div class="bg-white rounded-xl border border-zinc-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-zinc-500">Nama</label>
                    <p class="font-medium text-zinc-900">{{ $student->nama }}</p>
                </div>
                <div>
                    <label class="text-sm text-zinc-500">NIPD</label>
                    <p class="font-medium text-zinc-900 font-mono">{{ $student->nipd }}</p>
                </div>
                <div>
                    <label class="text-sm text-zinc-500">NISN</label>
                    <p class="font-medium text-zinc-900 font-mono">{{ $student->nisn }}</p>
                </div>
                <div>
                    <label class="text-sm text-zinc-500">Kelas</label>
                    <p class="font-medium text-zinc-900">{{ $student->class_name }}</p>
                </div>
                <div>
                    <label class="text-sm text-zinc-500">Jenis Kelamin</label>
                    <p class="font-medium text-zinc-900">{{ $student->jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                </div>
                <div>
                    <label class="text-sm text-zinc-500">Status</label>
                    <p class="font-medium {{ $student->status === 'aktif' ? 'text-emerald-600' : 'text-red-600' }}">{{ ucfirst($student->status) }}</p>
                </div>
                @if($student->hp)
                    <div>
                        <label class="text-sm text-zinc-500">No. HP</label>
                        <p class="font-medium text-zinc-900">{{ $student->hp }}</p>
                    </div>
                @endif
                @if($student->email)
                    <div>
                        <label class="text-sm text-zinc-500">Email</label>
                        <p class="font-medium text-zinc-900">{{ $student->email }}</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl border border-zinc-200 p-8 text-center">
            <p class="text-zinc-400">Profil siswa tidak ditemukan.</p>
        </div>
    @endif
</div>
