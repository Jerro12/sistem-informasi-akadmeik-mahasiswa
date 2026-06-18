<x-app-layout>
    <x-slot name="header">Monitoring Bimbingan Skripsi</x-slot>

    @if(session('success'))<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card-saas p-5 flex items-center gap-4 dark:bg-gray-800">
            <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['total_bimbingan'] }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Total Bimbingan</p>
            </div>
        </div>
        <div class="card-saas p-5 flex items-center gap-4 dark:bg-gray-800">
            <div class="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['mahasiswa_aktif'] }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Mahasiswa Skripsi Aktif</p>
            </div>
        </div>
        <div class="card-saas p-5 flex items-center gap-4 dark:bg-gray-800">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-siakad-dark dark:text-white">{{ $stats['bimbingan_bulan_ini'] }}</p>
                <p class="text-xs text-siakad-secondary dark:text-gray-400">Bimbingan Bulan Ini</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
        <a href="{{ route('admin.bimbingan.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.bimbingan.index') ? 'bg-siakad-primary text-white' : 'bg-white dark:bg-gray-800 text-siakad-secondary dark:text-gray-400 border border-siakad-light dark:border-gray-700' }}">
            Log Bimbingan
        </a>
        <a href="{{ route('admin.bimbingan.mahasiswa-monitor') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.bimbingan.mahasiswa-monitor') ? 'bg-siakad-primary text-white' : 'bg-white dark:bg-gray-800 text-siakad-secondary dark:text-gray-400 border border-siakad-light dark:border-gray-700' }}">
            Per Mahasiswa
        </a>
    </div>

    <!-- Filter -->
    <div class="card-saas p-4 mb-6 dark:bg-gray-800">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Cari Mahasiswa</label>
                <input type="text" name="search" value="{{ request('search') }}" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Nama atau NIM...">
            </div>
            <div class="w-56">
                <label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Filter Dosen Pembimbing</label>
                <select name="dosen_id" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Dosen</option>
                    @foreach($dosenList as $dosen)
                    <option value="{{ $dosen->id }}" {{ request('dosen_id') == $dosen->id ? 'selected' : '' }}>{{ $dosen->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Filter</button>
            @if(request()->anyFilled(['search', 'dosen_id']))
                <a href="{{ route('admin.bimbingan.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-siakad-dark dark:text-white hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase w-12">#</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Mahasiswa</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Dosen Pembimbing</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Catatan Mahasiswa</th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Status</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Berkas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($bimbinganList as $index => $b)
                    <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-900/30 transition">
                        <td class="py-3.5 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $bimbinganList->firstItem() + $index }}</td>
                        <td class="py-3.5 px-5">
                            <p class="font-medium text-siakad-dark dark:text-white">{{ $b->skripsi->mahasiswa->user->name ?? '-' }}</p>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $b->skripsi->mahasiswa->nim ?? '-' }}</p>
                        </td>
                        <td class="py-3.5 px-5 text-sm text-siakad-dark dark:text-gray-200">
                            {{ $b->dosen->user->name ?? '-' }}
                        </td>
                        <td class="py-3.5 px-5 text-sm text-siakad-dark dark:text-gray-200">
                            {{ $b->tanggal_bimbingan->format('d M Y') }}
                        </td>
                        <td class="py-3.5 px-5 text-sm text-siakad-secondary dark:text-gray-400 max-w-xs">
                            <p class="line-clamp-2">{{ $b->catatan_mahasiswa ?? '-' }}</p>
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-{{ $b->status_color }}-100 text-{{ $b->status_color }}-700 dark:bg-{{ $b->status_color }}-900/50 dark:text-{{ $b->status_color }}-400">
                                {{ $b->status_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            @if($b->file_dokumen)
                            <a href="{{ route('bimbingan.download', $b) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-siakad-primary dark:text-blue-400 hover:underline font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Unduh
                            </a>
                            @else
                            <span class="text-xs text-gray-400 italic">Tidak ada</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-siakad-secondary dark:text-gray-400">
                            Belum ada data bimbingan yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bimbinganList->hasPages())
        <div class="px-5 py-4 border-t border-siakad-light dark:border-gray-700">
            {{ $bimbinganList->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
