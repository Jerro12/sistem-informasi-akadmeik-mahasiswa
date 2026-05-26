<x-app-layout>
    <x-slot name="header">Monitoring Bimbingan – Per Mahasiswa</x-slot>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
        <a href="{{ route('admin.bimbingan.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white dark:bg-gray-800 text-siakad-secondary dark:text-gray-400 border border-siakad-light dark:border-gray-700 hover:bg-gray-100 transition">
            Log Bimbingan
        </a>
        <a href="{{ route('admin.bimbingan.mahasiswa-monitor') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-siakad-primary text-white">
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
            <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Cari</button>
        </form>
    </div>

    <!-- Table -->
    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">#</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Mahasiswa</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Judul Skripsi</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Pembimbing</th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Jml Bimbingan</th>
                        <th class="text-center py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Status Skripsi</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($skripsiList as $index => $skripsi)
                    <tr class="hover:bg-siakad-light/10 dark:hover:bg-gray-900/30 transition">
                        <td class="py-3.5 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $skripsiList->firstItem() + $index }}</td>
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-siakad-primary flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr($skripsi->mahasiswa->user->name ?? '-', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-siakad-dark dark:text-white">{{ $skripsi->mahasiswa->user->name ?? '-' }}</p>
                                    <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $skripsi->mahasiswa->nim ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-5 text-sm text-siakad-dark dark:text-gray-200 max-w-xs">
                            <p class="line-clamp-2" title="{{ $skripsi->judul }}">{{ Str::limit($skripsi->judul, 55) }}</p>
                        </td>
                        <td class="py-3.5 px-5 text-sm text-siakad-dark dark:text-gray-200">
                            @if($skripsi->pembimbing1)
                                <p>{{ $skripsi->pembimbing1->user->name }}</p>
                                @if($skripsi->pembimbing2)
                                <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $skripsi->pembimbing2->user->name }}</p>
                                @endif
                            @else
                                <span class="text-amber-600 dark:text-amber-400 text-xs italic">Belum ditentukan</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            <span class="text-xl font-bold {{ $skripsi->bimbingan_count < 4 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $skripsi->bimbingan_count }}
                            </span>
                            <p class="text-[10px] text-siakad-secondary dark:text-gray-400">sesi</p>
                        </td>
                        <td class="py-3.5 px-5 text-center">
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-{{ $skripsi->status_color }}-100 text-{{ $skripsi->status_color }}-700 dark:bg-{{ $skripsi->status_color }}-900/50 dark:text-{{ $skripsi->status_color }}-400">
                                {{ $skripsi->status_label }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            <a href="{{ route('admin.skripsi.show', $skripsi) }}" class="inline-flex items-center gap-1 text-sm text-siakad-primary dark:text-blue-400 hover:underline">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-siakad-secondary dark:text-gray-400">
                            Tidak ada data mahasiswa yang sedang mengerjakan skripsi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($skripsiList->hasPages())
        <div class="px-5 py-4 border-t border-siakad-light dark:border-gray-700">
            {{ $skripsiList->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
