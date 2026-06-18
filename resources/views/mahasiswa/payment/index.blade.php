<x-app-layout>
    <x-slot name="header">
        Pembayaran KRS
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Status Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-400 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 text-red-700 dark:text-red-400 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Billing Info -->
            <div class="md:col-span-2 space-y-6">
                <div class="card-saas p-6 dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-siakad-dark dark:text-white mb-4">Informasi Tagihan</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-siakad-light dark:border-gray-700">
                            <span class="text-siakad-secondary dark:text-gray-400">Semester Aktif</span>
                            <span class="font-semibold text-siakad-dark dark:text-white">{{ $tahunAktif->tahun }} {{ $tahunAktif->semester }}</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-siakad-light dark:border-gray-700">
                            <span class="text-siakad-secondary dark:text-gray-400">Biaya Administrasi/KRS</span>
                            <span class="font-semibold text-siakad-dark dark:text-white">Rp {{ number_format($biayaKrs, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-4">
                            <span class="text-lg font-bold text-siakad-dark dark:text-white">Total Bayar</span>
                            <span class="text-2xl font-bold text-siakad-primary dark:text-blue-400">Rp {{ number_format($biayaKrs, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($isPaid)
                        <div class="mt-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-500/30 rounded-xl flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <p class="font-bold text-emerald-800 dark:text-emerald-300">Pembayaran Terverifikasi</p>
                                <p class="text-sm text-emerald-600 dark:text-emerald-400">Anda sudah dapat melakukan pengisian KRS.</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('mahasiswa.krs.index') }}" class="w-full btn-primary-saas py-3 rounded-xl font-bold text-center block">
                                Lanjut ke Pengisian KRS
                            </a>
                        </div>
                    @elseif($pendingPayment)
                        <div class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-500/30 rounded-xl flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-300">
                                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="font-bold text-amber-800 dark:text-amber-300">Menunggu Verifikasi</p>
                                <p class="text-sm text-amber-600 dark:text-amber-400">Bukti pembayaran Anda sedang diproses oleh admin.</p>
                            </div>
                        </div>
                    @else
                        <!-- Proof Upload Form -->
                        <div class="mt-6 border-t border-siakad-light dark:border-gray-700 pt-6">
                            <h4 class="font-bold text-siakad-dark dark:text-white mb-3">Upload Bukti Transfer</h4>
                            <form action="{{ route('mahasiswa.pembayaran.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-siakad-secondary uppercase mb-2">Pilih File Bukti Pembayaran</label>
                                    <input type="file" name="bukti_transfer" required class="input-saas w-full px-4 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <p class="text-[10px] text-siakad-secondary mt-1">Format: JPG, JPEG, PNG, WEBP. Maksimal: 2MB.</p>
                                </div>
                                <button type="submit" class="w-full btn-primary-saas py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Kirim Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Payment History -->
                <div class="card-saas overflow-hidden dark:bg-gray-800">
                    <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700 flex justify-between items-center">
                        <h3 class="font-bold text-siakad-dark dark:text-white">Riwayat Transaksi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-siakad-light/30 dark:bg-gray-900 text-siakad-secondary dark:text-gray-400 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">Order ID</th>
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3">Bukti</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                                @forelse($pembayaran as $p)
                                    <tr class="dark:text-gray-300">
                                        <td class="px-6 py-4 font-mono text-xs">{{ $p->order_id }}</td>
                                        <td class="px-6 py-4">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4">
                                            @if($p->bukti_transfer)
                                                <a href="{{ route('mahasiswa.pembayaran.bukti', $p) }}" target="_blank" class="text-siakad-primary dark:text-blue-400 hover:underline text-xs flex items-center gap-1 font-semibold">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    Lihat Bukti
                                                </a>
                                            @else
                                                <span class="text-xs text-siakad-secondary">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($p->status === 'success')
                                                <span class="px-2 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 rounded text-[10px] font-bold uppercase tracking-wider">LUNAS</span>
                                            @elseif($p->status === 'pending')
                                                <span class="px-2 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 rounded text-[10px] font-bold uppercase tracking-wider">PENDING</span>
                                            @else
                                                <span class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 rounded text-[10px] font-bold uppercase tracking-wider">{{ $p->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-semibold">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-siakad-secondary dark:text-gray-400">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Steps Info -->
            <div class="space-y-6">
                <!-- Bank Transfer Account Info -->
                <div class="card-saas p-6 dark:bg-gray-800 border-l-4 border-siakad-primary">
                    <h3 class="font-bold text-siakad-dark dark:text-white mb-4">Rekening Tujuan</h3>
                    <div class="space-y-3">
                        <div class="p-3 bg-siakad-light/20 dark:bg-gray-900 rounded-lg">
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 font-semibold uppercase">Nama Bank</p>
                            <p class="font-bold text-siakad-dark dark:text-white text-base">BANK MANDIRI</p>
                        </div>
                        <div class="p-3 bg-siakad-light/20 dark:bg-gray-900 rounded-lg">
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 font-semibold uppercase">Nomor Rekening</p>
                            <p class="font-mono font-bold text-siakad-primary dark:text-blue-400 text-lg">123-456-789-0</p>
                        </div>
                        <div class="p-3 bg-siakad-light/20 dark:bg-gray-900 rounded-lg">
                            <p class="text-xs text-siakad-secondary dark:text-gray-400 font-semibold uppercase">Atas Nama</p>
                            <p class="font-bold text-siakad-dark dark:text-white text-sm">UNIVERSITAS SIAKAD MAHASISWA</p>
                        </div>
                    </div>
                </div>

                <div class="card-saas p-6 bg-gradient-to-br from-siakad-primary to-siakad-dark text-white border-none">
                    <h3 class="font-bold mb-4">Langkah Pembayaran</h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">1</div>
                            <p class="text-sm">Lakukan transfer ke rekening tujuan di atas</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">2</div>
                            <p class="text-sm">Simpan struk atau bukti transfer transaksi</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">3</div>
                            <p class="text-sm">Unggah bukti transfer tersebut pada form yang disediakan</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">4</div>
                            <p class="text-sm">Tunggu proses verifikasi manual oleh admin selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
