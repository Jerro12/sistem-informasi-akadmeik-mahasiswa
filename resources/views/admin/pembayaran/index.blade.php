<x-app-layout>
    <x-slot name="header">
        Monitoring Pembayaran
    </x-slot>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Daftar transaksi pembayaran mahasiswa</p>
    </div>

    <!-- Filters -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form action="{{ route('admin.pembayaran.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
                <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-1">Fakultas</label>
                <select name="fakultas_id" id="filterFakultas" class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Fakultas</option>
                    @foreach($fakultasList as $f)
                        <option value="{{ $f->id }}" {{ request('fakultas_id') == $f->id ? 'selected' : '' }}>{{ $f->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-1">Prodi</label>
                <select name="prodi_id" id="filterProdi" class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Prodi</option>
                    @foreach($prodiList as $p)
                        <option value="{{ $p->id }}" data-fakultas-id="{{ $p->fakultas_id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-1">Cari Mahasiswa</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="NIM atau Nama..." class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary-saas w-full py-2 rounded-lg text-sm font-medium">Filter</button>
                @if(request()->anyFilled(['tahun_akademik_id', 'status', 'fakultas_id', 'prodi_id', 'search']))
                    <a href="{{ route('admin.pembayaran.index') }}" class="btn-ghost-saas px-3 py-2 border rounded-lg text-sm text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Script for Dynamic Dropdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fakultasSelect = document.getElementById('filterFakultas');
            const prodiSelect = document.getElementById('filterProdi');
            const prodiOptions = Array.from(prodiSelect.options);

            function updateProdiOptions() {
                const selectedFakultasId = fakultasSelect.value;
                const currentProdiValue = prodiSelect.value;
                let isCurrentProdiValid = false;

                // First option (Semua Prodi) always visible
                prodiSelect.innerHTML = '';
                prodiSelect.appendChild(prodiOptions[0]);

                prodiOptions.slice(1).forEach(option => {
                    if (!selectedFakultasId || option.dataset.fakultasId === selectedFakultasId) {
                        prodiSelect.appendChild(option);
                        if (option.value === currentProdiValue) {
                            isCurrentProdiValid = true;
                        }
                    }
                });

                // Reset prodi selection if current selection is no longer valid
                if (currentProdiValue && !isCurrentProdiValid) {
                    prodiSelect.value = '';
                } else {
                    prodiSelect.value = currentProdiValue;
                }
            }

            fakultasSelect.addEventListener('change', updateProdiOptions);
            updateProdiOptions();
        });
    </script>

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
                        <th class="text-right py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Aksi</th>
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
                            <td class="py-4 px-4 text-right">
                                @if($p->bukti_transfer)
                                    <a href="{{ route('admin.pembayaran.show', $p) }}" target="_blank" class="inline-flex items-center gap-1 text-siakad-primary dark:text-blue-400 hover:underline text-[10px] font-bold uppercase tracking-wider mb-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Lihat Bukti
                                    </a>
                                @endif
                                @if($p->status !== 'success')
                                    <form action="{{ route('admin.pembayaran.verify', $p) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini secara manual?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-siakad-primary text-white text-[10px] font-bold rounded hover:bg-siakad-dark transition w-full text-center">
                                            VERIFIKASI
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-green-600 font-medium italic block">Verified</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-siakad-secondary">Tidak ada data pembayaran yang ditemukan</td>
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
