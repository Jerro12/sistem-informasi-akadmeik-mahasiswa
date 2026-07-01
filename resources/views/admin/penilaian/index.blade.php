<x-app-layout>
    <x-slot name="header">
        Input Nilai Mahasiswa
    </x-slot>
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-siakad-dark dark:text-white">Input Nilai Mahasiswa</h1>
            <p class="text-sm text-siakad-secondary dark:text-gray-400 mt-1">
                Pilih kelas untuk menginputkan atau memperbarui nilai mahasiswa.
            </p>
        </div>
        <form method="GET" action="{{ route('admin.penilaian.index') }}" class="flex items-center gap-2">
            <select name="tahun_akademik_id" class="input-saas px-3 py-2 text-sm max-w-[150px]">
                <option value="">Semua Tahun</option>
                @foreach($tahunAkademiks as $ta)
                <option value="{{ $ta->id }}" {{ request('tahun_akademik_id') == $ta->id ? 'selected' : '' }}>{{ $ta->tahun }} {{ ucfirst($ta->semester) }}</option>
                @endforeach
            </select>
            
            @if(Auth::user()->role !== 'admin_prodi')
            <select name="prodi_id" class="input-saas px-3 py-2 text-sm max-w-[180px]">
                <option value="">Semua Prodi</option>
                @foreach($prodis as $p)
                <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                @endforeach
            </select>
            @endif

            <input
                type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari kelas / mata kuliah..."
                class="input-saas px-3 py-2 text-sm w-48"
            />
            <button type="submit" class="px-4 py-2 bg-siakad-primary text-white rounded-lg text-sm font-medium hover:bg-siakad-primary/90 transition">
                Cari
            </button>
            @if(request('search') || request('tahun_akademik_id') || request('prodi_id'))
            <a href="{{ route('admin.penilaian.index') }}" class="px-3 py-2 text-sm text-siakad-secondary dark:text-gray-400 hover:text-siakad-dark dark:hover:text-white transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    <div class="card-saas dark:bg-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-siakad-light dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Dosen</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nilai Masuk</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($kelas as $k)
                    @php
                        $totalMhs   = $k->krsDetail->count();
                        $nilaiMasuk = $k->nilai->count();
                        $done       = $totalMhs > 0 && $nilaiMasuk >= $totalMhs;
                    @endphp
                    <tr class="hover:bg-siakad-light/30 dark:hover:bg-gray-700/30 transition">
                        <td class="px-4 py-3 text-sm font-semibold text-siakad-dark dark:text-white">{{ $k->nama_kelas }}</td>
                        <td class="px-4 py-3 text-sm text-siakad-dark dark:text-gray-200">
                            {{ $k->mataKuliah->nama_mk ?? '-' }}
                            <span class="text-xs text-siakad-secondary dark:text-gray-400 block">{{ $k->mataKuliah->kode_mk ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-siakad-dark dark:text-gray-200">{{ $k->dosen->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-sm text-siakad-dark dark:text-gray-200">{{ $totalMhs }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($done)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    Selesai ({{ $nilaiMasuk }})
                                </span>
                            @elseif($nilaiMasuk > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                    Sebagian ({{ $nilaiMasuk }}/{{ $totalMhs }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                    Belum ada
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.penilaian.show', $k->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-siakad-primary text-white rounded-lg text-xs font-medium hover:bg-siakad-primary/90 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Input Nilai
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-siakad-secondary dark:text-gray-400">
                            @if(request('search'))
                                Tidak ada kelas yang cocok dengan pencarian "<strong>{{ request('search') }}</strong>".
                            @else
                                Belum ada kelas yang tersedia.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $kelas->links() }}</div>
</div>
</x-app-layout>
