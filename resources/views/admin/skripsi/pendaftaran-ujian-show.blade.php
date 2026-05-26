<x-app-layout>
    <x-slot name="header">
        Detail Pendaftaran Ujian
    </x-slot>

    @if(session('success'))<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">{{ session('error') }}</div>@endif

    <div class="mb-6">
        <a href="{{ route('admin.pendaftaran-ujian.index') }}" class="inline-flex items-center gap-2 text-sm text-siakad-secondary hover:text-[#234C6A] dark:text-gray-400 dark:hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card-saas p-6 dark:bg-gray-800">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto rounded-xl bg-siakad-primary flex items-center justify-center text-white text-2xl font-bold mb-3">
                        {{ strtoupper(substr($ujian->mahasiswa->user->name ?? '-', 0, 1)) }}
                    </div>
                    <h2 class="text-lg font-bold text-siakad-dark dark:text-white">{{ $ujian->mahasiswa->user->name ?? '-' }}</h2>
                    <p class="text-xs font-mono text-siakad-secondary dark:text-gray-400 mt-0.5">{{ $ujian->mahasiswa->nim }}</p>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">{{ $ujian->mahasiswa->prodi->nama ?? '-' }}</p>
                </div>

                <div class="space-y-3 border-t dark:border-gray-700 pt-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Jenis Ujian:</span>
                        <span class="font-bold text-siakad-primary dark:text-blue-400 uppercase tracking-wider">Ujian {{ $ujian->jenis_ujian }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Judul Skripsi:</span>
                    </div>
                    <p class="text-xs text-siakad-dark dark:text-gray-200 bg-siakad-light/20 dark:bg-gray-900 p-2.5 rounded-lg border border-siakad-light/50 dark:border-gray-700 leading-relaxed">{{ $ujian->skripsi->judul }}</p>
                    
                    <div class="flex justify-between border-t dark:border-gray-700 pt-3">
                        <span class="text-siakad-secondary dark:text-gray-400">Status Pendaftaran:</span>
                        @if($ujian->status === 'approved')
                            <span class="px-2 py-0.5 text-xs font-bold bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-400 rounded-full">DISETUJUI</span>
                        @elseif($ujian->status === 'rejected')
                            <span class="px-2 py-0.5 text-xs font-bold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-400 rounded-full">DITOLAK / REVISI</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400 rounded-full">MENUNGGU VERIFIKASI</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approval / Rejection Final Panel -->
            @if($ujian->status === 'pending')
                <div class="card-saas p-6 dark:bg-gray-800 space-y-4">
                    <h3 class="font-bold text-siakad-dark dark:text-white border-b dark:border-gray-700 pb-3">Keputusan Akhir</h3>
                    
                    <!-- Final Approve -->
                    <form action="{{ route('admin.pendaftaran-ujian.approve', $ujian) }}" method="POST" onsubmit="return confirm('Setujui pendaftaran ujian ini dan jadwalkan?')">
                        @csrf
                        <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-emerald-600/20">
                            ✓ SETUJUI & AKTIFKAN UJIAN
                        </button>
                    </form>

                    <!-- Final Reject / Revision -->
                    <div class="border-t dark:border-gray-700 pt-4">
                        <form action="{{ route('admin.pendaftaran-ujian.reject', $ujian) }}" method="POST" onsubmit="return confirm('Tolak pendaftaran ini dengan catatan revisi?')">
                            @csrf
                            <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase mb-1">Alasan Penolakan / Catatan Revisi:</label>
                            <textarea name="catatan" required rows="3" class="input-saas w-full text-xs dark:bg-gray-700 dark:border-gray-600 dark:text-white mb-2" placeholder="Tuliskan catatan perbaikan berkas..."></textarea>
                            <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-red-600/20">
                                ✗ TOLAK / MINTA REVISI
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Requirements Verification and Scheduling -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 1. Verify Prerequisites -->
            <div class="card-saas overflow-hidden dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700 bg-siakad-primary/5">
                    <h3 class="font-bold text-siakad-dark dark:text-white">Verifikasi Persyaratan Dokumen</h3>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-0.5">Semua berkas harus disetujui untuk menyetujui pendaftaran.</p>
                </div>
                <div class="divide-y divide-siakad-light dark:divide-gray-700">
                    @foreach($ujian->syaratUpload as $syarat)
                        <div class="p-5 flex flex-col md:flex-row md:items-start justify-between gap-6 hover:bg-siakad-light/10 dark:hover:bg-gray-900/30 transition">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-{{ $syarat->status === 'approved' ? 'green' : ($syarat->status === 'rejected' ? 'red' : 'amber') }}-500"></span>
                                    <h4 class="font-semibold text-siakad-dark dark:text-white text-sm">{{ $syarat->nama_persyaratan }}</h4>
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    @if($syarat->file_path)
                                        <a href="{{ asset('storage/' . $syarat->file_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-siakad-primary dark:text-blue-400 hover:underline font-bold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Lihat Berkas Pendukung
                                        </a>
                                    @else
                                        <span class="text-xs text-red-500 italic">File tidak ditemukan/belum diunggah</span>
                                    @endif
                                </div>
                                @if($syarat->catatan)
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-2 bg-red-50 dark:bg-red-950/20 p-2 rounded border dark:border-red-900/30 font-medium">Catatan: {{ $syarat->catatan }}</p>
                                @endif
                            </div>

                            @if($ujian->status === 'pending')
                                <div class="flex-shrink-0">
                                    <form action="{{ route('admin.pendaftaran-ujian.verify-syarat', [$ujian, $syarat]) }}" method="POST" class="flex flex-col sm:flex-row items-end sm:items-center gap-2">
                                        @csrf
                                        <input type="text" name="catatan" value="{{ $syarat->catatan }}" placeholder="Catatan/Alasan..." class="input-saas px-3 py-1.5 text-xs w-48 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        
                                        <button type="submit" name="status" value="approved" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded transition flex items-center gap-1">
                                            ✓ ACC
                                        </button>
                                        <button type="submit" name="status" value="rejected" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white font-bold text-xs rounded transition flex items-center gap-1">
                                            ✗ REVISI
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="flex-shrink-0">
                                    @if($syarat->status === 'approved')
                                        <span class="px-3 py-1 text-xs font-bold bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400 rounded">DISETUJUI</span>
                                    @elseif($syarat->status === 'rejected')
                                        <span class="px-3 py-1 text-xs font-bold bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400 rounded">DITOLAK</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 rounded">BELUM VERIFIKASI</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 2. Set Schedule & Examiners -->
            <div class="card-saas p-6 dark:bg-gray-800">
                <h3 class="font-bold text-siakad-dark dark:text-white border-b dark:border-gray-700 pb-3 mb-5">Atur Jadwal & Tim Penguji</h3>
                
                @if($ujian->status === 'pending')
                    <form action="{{ route('admin.pendaftaran-ujian.set-jadwal', $ujian) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase mb-2">Tanggal Ujian</label>
                                <input type="date" name="tanggal_ujian" value="{{ $ujian->tanggal_ujian ? $ujian->tanggal_ujian->format('Y-m-d') : '' }}" required class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase mb-2">Jam Mulai</label>
                                <input type="time" name="jam_mulai" value="{{ $ujian->jam_mulai }}" required class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase mb-2">Jam Selesai</label>
                                <input type="time" name="jam_selesai" value="{{ $ujian->jam_selesai }}" required class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase mb-2">Ruangan</label>
                                <input type="text" name="ruangan" value="{{ $ujian->ruangan }}" required placeholder="Contoh: R. Sidang 2" class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase mb-2">Dosen Penguji 1</label>
                                <select name="penguji1_id" required class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="" disabled selected>-- Pilih Penguji 1 --</option>
                                    @foreach($dosenList as $d)
                                        <option value="{{ $d->id }}" {{ $ujian->penguji1_id == $d->id ? 'selected' : '' }}>{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase mb-2">Dosen Penguji 2</label>
                                <select name="penguji2_id" required class="input-saas w-full text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="" disabled selected>-- Pilih Penguji 2 --</option>
                                    @foreach($dosenList as $d)
                                        <option value="{{ $d->id }}" {{ $ujian->penguji2_id == $d->id ? 'selected' : '' }}>{{ $d->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3">
                            <button type="submit" class="btn-primary-saas px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-siakad-primary/20 transition-all hover:scale-105">
                                Simpan Jadwal & Penguji
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Read-only schedule details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-siakad-light/10 dark:bg-gray-900 p-5 rounded-xl border border-siakad-light/50 dark:border-gray-700">
                        <div>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 font-semibold uppercase">Jadwal Ujian</p>
                            <p class="text-sm font-bold text-siakad-dark dark:text-white mt-1">
                                Tanggal: {{ $ujian->tanggal_ujian ? $ujian->tanggal_ujian->format('d F Y') : '-' }} <br>
                                Waktu: {{ substr($ujian->jam_mulai, 0, 5) }} - {{ substr($ujian->jam_selesai, 0, 5) }} WIB <br>
                                Ruangan: <span class="text-[#234C6A] dark:text-blue-400">{{ $ujian->ruangan ?? '-' }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 font-semibold uppercase">Tim Penguji</p>
                            <p class="text-xs text-siakad-dark dark:text-gray-200 mt-1"><span class="font-semibold text-siakad-primary dark:text-blue-400">Penguji 1:</span> {{ $ujian->penguji1->user->name ?? '-' }}</p>
                            <p class="text-xs text-siakad-dark dark:text-gray-200 mt-1"><span class="font-semibold text-siakad-primary dark:text-blue-400">Penguji 2:</span> {{ $ujian->penguji2->user->name ?? '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
