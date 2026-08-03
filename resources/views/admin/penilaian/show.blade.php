<x-app-layout>
    <x-slot name="header">
        Input Nilai Detail
    </x-slot>
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-siakad-dark dark:text-white">Input Nilai</h1>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1">
                {{ $kelas->nama_kelas }} &mdash; {{ $kelas->mataKuliah->nama_mk ?? '-' }}
            </p>
        </div>
        <a href="{{ route('admin.penilaian.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-siakad-light dark:border-gray-600 text-sm text-siakad-secondary dark:text-gray-300 hover:bg-siakad-light dark:hover:bg-gray-700 transition">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-500/30 rounded-lg text-emerald-700 dark:text-emerald-300 text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-500/30 rounded-lg text-red-700 dark:text-red-300 text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- Buat lookup: mahasiswa_id → nilai_angka --}}
    @php
        $nilaiMap = $kelas->nilai->keyBy('mahasiswa_id');
    @endphp

    <form method="POST" action="{{ route('admin.penilaian.store', $kelas->id) }}">
        @csrf
        <div class="card-saas dark:bg-gray-800 overflow-hidden">
            <div class="p-5 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-base font-semibold text-siakad-dark dark:text-white">
                    Daftar Mahasiswa ({{ $kelas->krsDetail->count() }} orang)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-siakad-light dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nama Mahasiswa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">NIM</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nilai (0-100)</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                        @forelse($kelas->krsDetail as $i => $detail)
                        @php
                            $mahasiswaId = $detail->krs->mahasiswa_id ?? null;
                            $nilaiAngka  = $mahasiswaId ? ($nilaiMap->get($mahasiswaId)->nilai_angka ?? '') : '';
                            $nilaiHuruf  = $mahasiswaId ? ($nilaiMap->get($mahasiswaId)->nilai_huruf ?? '-') : '-';
                        @endphp
                        <tr class="hover:bg-siakad-light/30 dark:hover:bg-gray-700/30 transition">
                            <td class="px-4 py-3 text-sm text-siakad-secondary dark:text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-siakad-dark dark:text-white">
                                {{ $detail->krs->mahasiswa->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-siakad-secondary dark:text-gray-400">
                                {{ $detail->krs->mahasiswa->nim ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="number"
                                    name="nilai[{{ $detail->id }}]"
                                    value="{{ $nilaiAngka }}"
                                    min="0" max="100" step="0.1"
                                    class="input-saas w-24 text-center"
                                    placeholder="—"
                                    {{ Auth::user()->role === 'admin_fakultas' ? 'disabled readonly' : '' }}
                                />
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-sm font-bold
                                    {{ $nilaiHuruf === 'A' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' :
                                       ($nilaiHuruf === 'B' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' :
                                       ($nilaiHuruf === 'C' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' :
                                       ($nilaiHuruf === 'D' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' :
                                       ($nilaiHuruf === 'E' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' :
                                       ($nilaiHuruf === 'T' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' :
                                       'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'))))) }}">
                                    {{ $nilaiHuruf }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-siakad-secondary dark:text-gray-400">
                                Belum ada mahasiswa yang mendaftar di kelas ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($kelas->krsDetail->count() > 0)
        <div class="flex justify-end mt-5">
            @if(Auth::user()->role !== 'admin_fakultas')
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-siakad-primary text-white rounded-lg text-sm font-semibold hover:bg-siakad-primary/90 transition shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Semua Nilai
            </button>
            @else
            <div class="px-4 py-2 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-xs font-medium">
                Akses Admin Fakultas (Monitoring Only)
            </div>
            @endif
        </div>
        @endif
    </form>
</div>
</x-app-layout>
