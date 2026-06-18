<x-app-layout>
    <x-slot name="header">
        Jadwal Kuliah
    </x-slot>

    @php
        $today = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
        $now = \Carbon\Carbon::now();
        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    @endphp

    <div x-data="{ selectedDay: '{{ $today }}', viewMode: 'card' }">
        @if(!$activeTA)
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-6 text-center max-w-md mx-auto">
            <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="font-semibold text-amber-800 mb-1">Tidak Ada Tahun Akademik Aktif</h3>
            <p class="text-sm text-amber-600">Hubungi admin untuk mengaktifkan tahun akademik.</p>
        </div>
        @elseif($jadwalPerHari->isEmpty())
        <div class="card-saas p-10 text-center max-w-md mx-auto">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Ada Jadwal</h3>
            <p class="text-slate-500 mb-5">KRS Anda belum diapprove atau belum ada jadwal kuliah.</p>
            <a href="{{ route('mahasiswa.krs.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#234C6A] text-white rounded-lg text-sm font-medium hover:bg-[#1B3C53] transition">
                Lihat KRS
            </a>
        </div>
        @else
        
        <!-- Schedule Table (Academic Style) -->
        <div class="card-saas overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-siakad-primary text-white">
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider border-r border-white/10 w-32">Hari</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider border-r border-white/10 w-40">Waktu</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider border-r border-white/10">Mata Kuliah</th>
                            <th class="px-4 py-4 text-center font-bold uppercase tracking-wider border-r border-white/10 w-20">SKS</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider border-r border-white/10">Dosen</th>
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider">Ruangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($hariOrder as $hari)
                            @if($jadwalPerHari->has($hari))
                                @php $items = $jadwalPerHari[$hari]; @endphp
                                @foreach($items as $index => $item)
                                @php
                                    $kelas = $item['kelas'];
                                    $jadwal = $item['jadwal'];
                                    $jamMulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
                                    $jamSelesai = \Carbon\Carbon::parse($jadwal->jam_selesai);
                                    $isOngoing = $hari === $today && $now->between($jamMulai, $jamSelesai);
                                @endphp
                                <tr class="{{ $isOngoing ? 'bg-siakad-primary/5 dark:bg-siakad-primary/10' : '' }} hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    @if($index === 0)
                                    <td class="px-6 py-4 font-bold text-siakad-primary bg-slate-50/50 dark:bg-slate-900/50 align-top border-r border-slate-200 dark:border-slate-700" rowspan="{{ $items->count() }}">
                                        <div class="sticky top-24">
                                            {{ $hari }}
                                            @if($hari === $today)
                                            <span class="block mt-1 text-[10px] text-emerald-500 uppercase tracking-tighter">(Hari Ini)</span>
                                            @endif
                                        </div>
                                    </td>
                                    @endif
                                    <td class="px-6 py-4 border-r border-slate-200 dark:border-slate-700">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-siakad-dark dark:text-slate-200">{{ $jamMulai->format('H:i') }} - {{ $jamSelesai->format('H:i') }}</span>
                                            @if($isOngoing)
                                            <span class="text-[10px] text-emerald-500 font-medium flex items-center gap-1 mt-1">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                                Sedang Berlangsung
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 border-r border-slate-200 dark:border-slate-700">
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-siakad-dark dark:text-slate-200">{{ $kelas->mataKuliah->nama_mk }}</span>
                                            <span class="text-xs text-siakad-secondary dark:text-slate-400">{{ $kelas->mataKuliah->kode_mk }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center font-bold text-siakad-primary border-r border-slate-200 dark:border-slate-700">
                                        {{ $kelas->mataKuliah->sks }}
                                    </td>
                                    <td class="px-6 py-4 border-r border-slate-200 dark:border-slate-700">
                                        <span class="text-siakad-secondary dark:text-slate-300">{{ $kelas->dosen->user->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($jadwal->ruangan)
                                        <span class="px-2.5 py-1 bg-siakad-primary/10 text-siakad-primary dark:bg-blue-500/10 dark:text-blue-400 rounded-lg font-bold text-xs">
                                            {{ $jadwal->ruangan }}
                                        </span>
                                        @else
                                        <span class="text-slate-400 italic">TBA</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="mt-8 bg-[#1B3C53] rounded-2xl p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold">Ringkasan Jadwal</p>
                        <p class="text-white/60 text-sm">{{ $activeTA->tahun }} • Semester {{ $activeTA->semester }}</p>
                    </div>
                </div>
                <div class="flex justify-between w-full sm:w-auto sm:justify-start gap-4 sm:gap-8">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white text-xl font-bold">{{ $jadwalPerHari->flatten(1)->count() }}</p>
                            <p class="text-white/60 text-xs">Sesi Kuliah</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <p class="text-white text-xl font-bold">{{ $jadwalPerHari->keys()->count() }}</p>
                            <p class="text-white/60 text-xs">Hari Aktif</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <style>[x-cloak] { display: none !important; }</style>
</x-app-layout>
