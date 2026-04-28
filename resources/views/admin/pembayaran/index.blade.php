<x-app-layout>
    <x-slot name="header">
        Monitoring Pembayaran
    </x-slot>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Daftar transaksi pembayaran mahasiswa</p>
    </div>

    <!-- Filters -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form action="{{ route('admin.pembayaran.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-1">Tahun Akademik</label>
                <select name="tahun_akademik_id" class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Semester</option>
                    @foreach($tahunAkademik as $ta)
                        <option value="{{ $ta->id }}" {{ request('tahun_akademik_id') == $ta->id ? 'selected' : '' }}>
                            {{ $ta->tahun }} {{ $ta->semester }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-1">Status</label>
                <select name="status" class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success (Lunas)</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-1">Cari Mahasiswa</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="NIM atau Nama..." class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary-saas w-full py-2 rounded-lg text-sm font-medium">Filter</button>
            </div>
        </form>
    </div>

    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Waktu</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Mahasiswa</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Semester</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Order ID</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Nominal</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($pembayaran as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="py-4 px-4 text-sm text-siakad-secondary dark:text-gray-400">
                                {{ $p->created_at->format('d/m/Y') }}<br>
                                <span class="text-[10px] opacity-70">{{ $p->created_at->format('H:i') }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="text-sm font-medium text-siakad-dark dark:text-white">{{ $p->mahasiswa->user->name }}</div>
                                <div class="text-xs text-siakad-secondary font-mono">{{ $p->mahasiswa->nim }}</div>
                                <div class="text-[10px] text-siakad-secondary opacity-70">{{ $p->mahasiswa->prodi->nama ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 text-sm text-siakad-secondary">
                                {{ $p->tahunAkademik->tahun }}<br>
                                <span class="text-[10px] capitalize">{{ $p->tahunAkademik->semester }}</span>
                            </td>
                            <td class="py-4 px-4 text-xs font-mono text-siakad-secondary">{{ $p->order_id }}</td>
                            <td class="py-4 px-4 text-sm font-semibold text-siakad-dark dark:text-white">
                                Rp {{ number_format($p->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4">
                                @if($p->status === 'success')
                                    <span class="inline-flex px-2.5 py-1 text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 rounded uppercase tracking-wider">SUCCESS</span>
                                @elseif($p->status === 'pending')
                                    <span class="inline-flex px-2.5 py-1 text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 rounded uppercase tracking-wider">PENDING</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 rounded uppercase tracking-wider">{{ $p->status }}</span>
                                @endif
                                <br>
                                <span class="text-[9px] text-siakad-secondary opacity-70">{{ $p->payment_type }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-siakad-secondary">Tidak ada data pembayaran yang ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pembayaran->hasPages())
        <div class="mt-4">
            {{ $pembayaran->appends(request()->query())->links() }}
        </div>
    @endif
</x-app-layout>
