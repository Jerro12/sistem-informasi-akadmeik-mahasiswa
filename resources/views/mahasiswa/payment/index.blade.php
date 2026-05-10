<x-app-layout>
    <x-slot name="header">
        Pembayaran KRS
    </x-slot>

    <div class="max-w-4xl mx-auto">
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

                    @if(!$isPaid)
                        <div class="mt-6">
                            @if($snapToken)
                                <button id="pay-button" class="w-full btn-primary-saas py-3 rounded-xl font-bold text-lg flex items-center justify-center gap-2 transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    Bayar Sekarang
                                </button>
                            @else
                                <div class="p-4 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
                                    <p class="font-bold mb-1">Gagal menghubungkan ke sistem pembayaran.</p>
                                    <p class="opacity-80">{{ $errorMessage ?? 'Silakan muat ulang halaman atau hubungi admin.' }}</p>
                                </div>
                            @endif
                        </div>
                    @else
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
                    @endif
                </div>

                <!-- Payment History -->
                <div class="card-saas overflow-hidden dark:bg-gray-800">
                    <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                        <h3 class="font-bold text-siakad-dark dark:text-white">Riwayat Transaksi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-siakad-light/30 dark:bg-gray-900 text-siakad-secondary dark:text-gray-400 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">Order ID</th>
                                    <th class="px-6 py-3">Tanggal</th>
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
                                        <td colspan="4" class="px-6 py-8 text-center text-siakad-secondary dark:text-gray-400">Belum ada transaksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Steps Info -->
            <div class="space-y-6">
                <div class="card-saas p-6 bg-gradient-to-br from-siakad-primary to-siakad-dark text-white border-none">
                    <h3 class="font-bold mb-4">Langkah Pembayaran</h3>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">1</div>
                            <p class="text-sm">Klik tombol "Bayar Sekarang"</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">2</div>
                            <p class="text-sm">Pilih metode pembayaran (Transfer Bank, E-Wallet, dll)</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">3</div>
                            <p class="text-sm">Lakukan pembayaran sesuai instruksi</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">4</div>
                            <p class="text-sm">Tunggu status berubah menjadi Lunas</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold">5</div>
                            <p class="text-sm">Isi KRS pada menu yang disediakan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$isPaid && $snapToken)
        @push('scripts')
            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
            <script type="text/javascript">
                const payButton = document.getElementById('pay-button');
                payButton.onclick = function () {
                    window.snap.pay('{{ $snapToken }}', {
                        onSuccess: function(result){
                            window.location.reload();
                        },
                        onPending: function(result){
                            window.location.reload();
                        },
                        onError: function(result){
                            alert("Pembayaran gagal!");
                            window.location.reload();
                        },
                        onClose: function(){
                            console.log('Customer closed the popup without finishing the payment');
                        }
                    });
                };
            </script>
        @endpush
    @endif
</x-app-layout>
