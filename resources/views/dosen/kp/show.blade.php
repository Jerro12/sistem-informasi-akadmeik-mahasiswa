<x-app-layout>
    <x-slot name="header">Detail KP</x-slot>
    <div class="mb-6"><a href="{{ route('dosen.kp.index') }}" class="text-siakad-secondary hover:text-siakad-primary text-sm">← Kembali</a></div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card-saas p-6">
                <div class="flex items-start justify-between mb-4">
                    <div><span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $kp->status_color }}-100 text-{{ $kp->status_color }}-700 dark:bg-gray-800 dark:text-{{ $kp->status_color }}-400 border dark:border-{{ $kp->status_color }}-400/20">{{ $kp->status_label }}</span><h2 class="text-lg font-bold text-siakad-dark dark:text-white mt-3">{{ $kp->nama_perusahaan }}</h2></div>
                    <span class="text-2xl font-bold text-siakad-primary dark:text-blue-400">{{ $kp->progress_percent }}%</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="p-3 rounded-lg bg-siakad-light/30 dark:bg-gray-700/50"><p class="text-xs text-siakad-secondary dark:text-gray-400">Mahasiswa</p><p class="font-medium text-siakad-dark dark:text-white">{{ $kp->mahasiswa->user->name }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->mahasiswa->nim }}</p></div>
                    <div class="p-3 rounded-lg bg-siakad-light/30 dark:bg-gray-700/50"><p class="text-xs text-siakad-secondary dark:text-gray-400">Periode</p><p class="font-medium text-siakad-dark dark:text-white">{{ $kp->tanggal_mulai->format('d M') }} - {{ $kp->tanggal_selesai->format('d M Y') }}</p></div>
                </div>
                <form action="{{ route('dosen.kp.update-status', $kp) }}" method="POST" class="mt-4 flex items-end gap-3">@csrf @method('PUT')
                    <div class="flex-1"><label class="block text-xs font-medium text-siakad-dark dark:text-gray-300 mb-1">Update Status</label><select name="status" class="input-saas w-full text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">@foreach(\App\Models\KerjaPraktek::getStatusList() as $k => $v)<option value="{{ $k }}" {{ $kp->status === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach</select></div>
                    <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Update</button>
                </form>
            </div>

            {{-- Approval Panel untuk KP --}}
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Status Penerimaan Pembimbingan KP</h3>
                @if($kp->pembimbing_approved === true)
                    <div class="flex items-center gap-3 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                        <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-medium text-emerald-800">Anda telah menyetujui penugasan KP ini</p>
                            <p class="text-sm text-emerald-600">Mahasiswa: {{ $kp->mahasiswa->user->name }}</p>
                        </div>
                    </div>
                @elseif($kp->pembimbing_approved === false)
                    <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="font-medium text-red-800">Anda telah menolak penugasan ini</p>
                        </div>
                        @if($kp->pembimbing_catatan)
                        <p class="text-sm text-red-700"><strong>Alasan:</strong> {{ $kp->pembimbing_catatan }}</p>
                        @endif
                    </div>
                @else
                    <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"></path></svg>
                            <p class="text-sm font-medium text-amber-800">Anda ditugaskan sebagai pembimbing KP. Harap berikan keputusan Anda.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('dosen.kp.approve', $kp) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Setujui
                            </button>
                        </form>
                        <button onclick="document.getElementById('modal-reject-kp').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Tolak
                        </button>
                    </div>
                    <div id="modal-reject-kp" class="hidden fixed inset-0 z-50 bg-black/40">
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                            <h4 class="font-bold text-siakad-dark mb-2">Tolak Penugasan Pembimbingan KP</h4>
                            <p class="text-sm text-siakad-secondary mb-4">Berikan alasan penolakan. Admin akan diberitahu.</p>
                            <form action="{{ route('dosen.kp.reject', $kp) }}" method="POST">
                                @csrf
                                <textarea name="catatan" rows="3" class="input-saas w-full text-sm mb-4"
                                    placeholder="Tuliskan alasan penolakan (min. 5 karakter)..." required minlength="5"></textarea>
                                <div class="flex gap-3 justify-end">
                                    <button type="button" onclick="document.getElementById('modal-reject-kp').classList.add('hidden')"
                                        class="px-4 py-2 text-sm text-siakad-secondary hover:text-siakad-dark transition">Batal</button>
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Konfirmasi Tolak</button>
                                </div>
                            </form>
                        </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700"><h3 class="font-semibold text-siakad-dark dark:text-white">Logbook ({{ $kp->logbook->count() }})</h3></div>
                @forelse($kp->logbook as $log)
                <div class="p-4 border-b border-siakad-light/50 dark:border-gray-700/50">
                    <div class="flex items-start gap-4">
                        <div class="text-center"><p class="text-lg font-bold text-siakad-primary dark:text-blue-400">{{ $log->tanggal->format('d') }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $log->tanggal->format('M') }}</p></div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1"><span class="text-xs text-siakad-secondary dark:text-gray-400">{{ $log->jam_masuk ?? '-' }} - {{ $log->jam_keluar ?? '-' }}</span><span class="px-2 py-0.5 text-xs rounded-full bg-{{ $log->status_color }}-100 text-{{ $log->status_color }}-700 dark:bg-gray-800 dark:text-{{ $log->status_color }}-400 border dark:border-{{ $log->status_color }}-400/20">{{ $log->status_label }}</span></div>
                            <p class="text-sm text-siakad-dark dark:text-gray-300">{{ $log->kegiatan }}</p>
                            @if($log->catatan_pembimbing)<p class="text-xs text-emerald-600 mt-2 p-2 bg-emerald-50 rounded">{{ $log->catatan_pembimbing }}</p>
                            @elseif($log->status === 'pending')
                            <form action="{{ route('dosen.kp.logbook.review', $log) }}" method="POST" class="mt-2 flex items-center gap-2">@csrf
                                <input type="text" name="catatan_pembimbing" class="input-saas flex-1 text-xs py-1" placeholder="Catatan...">
                                <button type="submit" name="status" value="disetujui" class="px-2 py-1 text-xs bg-emerald-600 text-white rounded">✓</button>
                                <button type="submit" name="status" value="revisi" class="px-2 py-1 text-xs bg-red-600 text-white rounded">✗</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-siakad-secondary">Belum ada logbook</div>
                @endforelse
            </div>
        </div>
        <div class="space-y-6">
            <div class="card-saas p-5"><h3 class="font-semibold text-siakad-dark dark:text-white mb-3">Info Perusahaan</h3><div class="text-sm space-y-2"><p class="text-siakad-dark dark:text-gray-300">{{ $kp->nama_perusahaan }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->alamat_perusahaan }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">Bidang: {{ $kp->bidang_usaha ?? '-' }}</p></div></div>
            <div class="card-saas p-5"><h3 class="font-semibold text-siakad-dark dark:text-white mb-3">Pembimbing Lapangan</h3><p class="text-sm text-siakad-dark dark:text-gray-300">{{ $kp->nama_pembimbing_lapangan ?? '-' }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->jabatan_pembimbing_lapangan }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->no_telp_pembimbing }}</p></div>
        </div>
    </div>
</x-app-layout>
