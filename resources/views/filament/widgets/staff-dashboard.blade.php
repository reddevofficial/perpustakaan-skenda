<div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($stats as $stat)
            @php
                $colorClasses = match($stat['color'] ?? 'gray') {
                    'warning' => 'bg-amber-50 text-amber-700',
                    'info' => 'bg-blue-50 text-blue-700',
                    'danger' => 'bg-red-50 text-red-700',
                    'success' => 'bg-emerald-50 text-emerald-700',
                    default => 'bg-zinc-50 text-zinc-700',
                };
                $iconColor = match($stat['color'] ?? 'gray') {
                    'warning' => 'text-amber-500',
                    'info' => 'text-blue-500',
                    'danger' => 'text-red-500',
                    'success' => 'text-emerald-500',
                    default => 'text-zinc-500',
                };
            @endphp
            <div class="bg-white rounded-xl border border-zinc-200 p-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="{{ $iconColor }}">
                        <x-dynamic-component :component="$stat['icon']" class="w-5 h-5" />
                    </div>
                    <span class="text-xs font-medium text-zinc-500">{{ $stat['label'] }}</span>
                </div>
                <p class="text-2xl font-bold text-zinc-900">{{ $stat['value'] }}</p>
                <p class="text-xs text-zinc-400 mt-1">{{ $stat['description'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-zinc-200">
        <div class="px-5 py-4 border-b border-zinc-200">
            <h2 class="text-lg font-semibold text-zinc-900">Peminjaman yang harus segera ditindaklanjuti</h2>
        </div>
        @if(empty($urgentLoans))
            <div class="p-8 text-center text-zinc-400 text-sm">
                Tidak ada peminjaman yang perlu ditindaklanjuti.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100">
                            <th class="text-left px-5 py-3 text-xs font-medium text-zinc-500 uppercase">No. Peminjaman</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-zinc-500 uppercase">Siswa</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-zinc-500 uppercase">Status</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-zinc-500 uppercase">Jatuh Tempo</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-zinc-500 uppercase">Buku</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($urgentLoans as $loan)
                            @php
                                $statusColor = match($loan['status'] ?? '') {
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'borrowed' => 'bg-blue-100 text-blue-700',
                                    'overdue' => 'bg-red-100 text-red-700',
                                    default => 'bg-zinc-100 text-zinc-700',
                                };
                                $statusLabel = match($loan['status'] ?? '') {
                                    'pending' => 'Menunggu Persetujuan',
                                    'borrowed' => 'Sedang Dipinjam',
                                    'overdue' => 'Terlambat',
                                    default => $loan['status'],
                                };
                            @endphp
                            <tr class="hover:bg-zinc-50">
                                <td class="px-5 py-3 font-mono text-zinc-700">{{ $loan['loan_number'] }}</td>
                                <td class="px-5 py-3 text-zinc-900">{{ $loan['student']['nama'] ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="px-5 py-3 text-zinc-700">{{ \Carbon\Carbon::parse($loan['due_at'])->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-zinc-600">
                                    @foreach($loan['items'] as $item)
                                        {{ $item['book_copy']['book']['title'] ?? '-' }}@if(!$loop->last), @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
