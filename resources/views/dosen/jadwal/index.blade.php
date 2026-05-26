<x-app-layout>
    <x-slot name="header">
        Jadwal Mengajar
    </x-slot>

    @php
        $today = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
        $now = \Carbon\Carbon::now();
        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    @endphp

    <div x-data="{ selectedDay: '{{ $today }}', viewMode: 'card' }">
        <!-- Filter Semester -->
        <div class="mb-6 card-saas p-4">
            <form action="{{ route('dosen.jadwal.index') }}" method="GET" class="flex gap-4 items-end">
                <div class="flex-1 max-w-sm">
                    <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-1">Pilih Tahun Akademik</label>
                    <select name="tahun_akademik_id" class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" onchange="this.form.submit()">
                        @foreach($tahunAkademik as $ta)
                            <option value="{{ $ta->id }}" {{ $selectedTaId == $ta->id ? 'selected' : '' }}>
                                {{ $ta->tahun }} - Semester {{ ucfirst($ta->semester) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if($jadwalPerHari->isEmpty())
        <div class="card-saas p-10 text-center max-w-md mx-auto">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Ada Jadwal</h3>
            <p class="text-slate-500 mb-5">Anda belum memiliki jadwal mengajar pada semester ini.</p>
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
                            <th class="px-6 py-4 text-left font-bold uppercase tracking-wider border-r border-white/10">Kelas</th>
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
                                        <span class="text-siakad-secondary dark:text-slate-300">{{ $kelas->nama_kelas }}</span>
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
                        @php $ta = $tahunAkademik->firstWhere('id', $selectedTaId); @endphp
                        <p class="text-white/60 text-sm">{{ $ta ? $ta->tahun . ' • Semester ' . ucfirst($ta->semester) : 'Semester Terpilih' }}</p>
                    </div>
                </div>
                <div class="flex justify-between w-full sm:w-auto sm:justify-start gap-4 sm:gap-8">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white text-xl font-bold">{{ $jadwalPerHari->flatten(1)->count() }}</p>
                            <p class="text-white/60 text-xs">Sesi Mengajar</p>
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
