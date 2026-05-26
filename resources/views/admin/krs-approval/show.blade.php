<x-app-layout>
    <x-slot name="header">
        Detail KRS - {{ $krs->mahasiswa->user->name ?? 'Unknown' }}
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Student Info -->
        <div class="lg:col-span-1">
            <div class="card-saas p-6 sticky top-24 dark:bg-gray-800">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-siakad-primary to-siakad-dark flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                        {{ strtoupper(substr($krs->mahasiswa->user->name ?? 'X', 0, 1)) }}
                    </div>
                    <h3 class="text-xl font-bold text-siakad-dark dark:text-white">{{ $krs->mahasiswa->user->name ?? '-' }}</h3>
                    <p class="text-siakad-secondary dark:text-gray-400">{{ $krs->mahasiswa->nim ?? '-' }}</p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Prodi</span>
                        <span class="font-medium text-siakad-dark dark:text-white">{{ $krs->mahasiswa->prodi->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Angkatan</span>
                        <span class="font-medium text-siakad-dark dark:text-white">{{ $krs->mahasiswa->angkatan ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Tahun Akademik</span>
                        <span class="font-medium text-siakad-dark dark:text-white">{{ $krs->tahunAkademik->tahun ?? '-' }} {{ $krs->tahunAkademik->semester ?? '' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Total SKS</span>
                        <span class="font-bold text-siakad-primary dark:text-blue-400 text-lg">{{ $totalSks }}</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-siakad-light dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <span class="text-siakad-secondary dark:text-gray-400">Status KRS</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize
                            {{ $krs->status == 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 dark:border dark:border-emerald-500/20' : 
                               ($krs->status == 'pending' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 dark:border dark:border-amber-500/20' : 
                               ($krs->status == 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 dark:border dark:border-red-500/20' : 'bg-slate-100 text-slate-800 dark:bg-gray-700 dark:text-gray-300 dark:border dark:border-gray-600')) }}">
                            {{ $krs->status }}
                        </span>
                    </div>
                </div>

                @if($krs->status === 'pending')
                <div class="mt-6 space-y-2">
                    <form action="{{ route('admin.krs-approval.approve', $krs->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-siakad-primary text-white rounded-lg text-sm font-medium hover:bg-siakad-primary/90 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Setujui KRS
                        </button>
                    </form>
                    <button type="button" onclick="showRejectModal()" class="w-full py-2.5 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-950/20 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Tolak KRS
                    </button>
                </div>
                @endif

                <a href="{{ url('admin/krs-approval') }}" class="mt-6 block text-center text-sm text-siakad-secondary dark:text-gray-400 hover:text-siakad-primary dark:hover:text-blue-400 transition">
                    ← Kembali ke daftar
                </a>
            </div>
        </div>

        <!-- Course List -->
        <div class="lg:col-span-2">
            <div class="card-saas overflow-hidden dark:bg-gray-800">
                <div class="p-6 border-b border-siakad-light dark:border-gray-700">
                    <h3 class="text-lg font-bold text-siakad-dark dark:text-white">Mata Kuliah Diambil</h3>
                    <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $krs->krsDetail->count() }} mata kuliah</p>
                </div>

                <div class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($krs->krsDetail as $detail)
                    <div class="p-4 flex items-center gap-4 hover:bg-siakad-light/30 dark:hover:bg-gray-700/50 transition">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-siakad-primary to-siakad-dark flex items-center justify-center text-white font-bold text-lg">
                            {{ $detail->kelas->nama_kelas ?? '-' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-siakad-dark dark:text-white">{{ $detail->kelas->mataKuliah->nama_mk ?? '-' }}</p>
                            <p class="text-sm text-siakad-secondary dark:text-gray-400">{{ $detail->kelas->mataKuliah->kode_mk ?? '-' }} • {{ $detail->kelas->dosen->user->name ?? '-' }}</p>
                        </div>
                        <div class="text-center">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-siakad-primary/10 text-siakad-primary dark:bg-blue-500/20 dark:text-blue-400 font-bold">
                                {{ $detail->kelas->mataKuliah->sks ?? 0 }}
                            </span>
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 mt-1">SKS</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-siakad-secondary dark:text-gray-400">
                        Tidak ada mata kuliah terdaftar
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-xl animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tolak KRS</h3>
            </div>
            <form action="{{ route('admin.krs-approval.reject', $krs->id) }}" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-sm font-medium text-siakad-dark dark:text-white mb-2">Alasan Penolakan</label>
                    <textarea name="catatan" rows="4" class="input-saas w-full resize-none bg-white dark:bg-gray-900" placeholder="Masukkan alasan mengapa KRS ditolak... (opsional)"></textarea>
                    <p class="text-xs text-siakad-secondary mt-2">Catatan ini akan dilihat oleh mahasiswa sebagai alasan penolakan.</p>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="hideRejectModal()" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm dark:text-gray-300 dark:hover:text-white">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Tolak KRS</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
