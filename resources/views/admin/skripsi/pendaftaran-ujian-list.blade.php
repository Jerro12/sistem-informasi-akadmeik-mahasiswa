<x-app-layout>
    <x-slot name="header">
        Pendaftaran Ujian Mahasiswa
    </x-slot>

    @if(session('success'))<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">{{ session('error') }}</div>@endif

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Verifikasi berkas persyaratan dan kelola jadwal ujian proposal, hasil, dan sidang akhir.</p>
        </div>
        <div>
            <a href="{{ route('admin.syarat-ujian.index') }}" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Kelola Persyaratan Ujian
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 card-saas p-4 dark:bg-gray-800">
        <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
            <select name="jenis_ujian" class="input-saas px-4 py-2 text-sm w-full sm:w-48 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <option value="">Semua Ujian</option>
                <option value="proposal" {{ request('jenis_ujian') == 'proposal' ? 'selected' : '' }}>Proposal</option>
                <option value="hasil" {{ request('jenis_ujian') == 'hasil' ? 'selected' : '' }}>Hasil</option>
                <option value="sidang" {{ request('jenis_ujian') == 'sidang' ? 'selected' : '' }}>Sidang Akhir</option>
            </select>

            <select name="status" class="input-saas px-4 py-2 text-sm w-full sm:w-48 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (Dijadwalkan)</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected (Revisi)</option>
            </select>

            <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto">Filter</button>
            
            @if(request()->anyFilled(['jenis_ujian', 'status']))
                <a href="{{ route('admin.pendaftaran-ujian.index') }}" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto text-center">Reset</a>
            @endif
        </form>
    </div>

    <!-- List Table -->
    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-16">#</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Mahasiswa</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Program Studi</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Jenis Ujian</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Tanggal & Waktu Ujian</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($ujianList as $index => $u)
                        <tr class="border-b border-siakad-light/50 dark:border-gray-700/50 hover:bg-siakad-light/10 dark:hover:bg-gray-900/30 transition">
                            <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $ujianList->firstItem() + $index }}</td>
                            <td class="py-4 px-5">
                                <div class="font-medium text-siakad-dark dark:text-white">{{ $u->mahasiswa->user->name ?? '-' }}</div>
                                <div class="text-xs text-siakad-secondary font-mono dark:text-gray-400">{{ $u->mahasiswa->nim }}</div>
                            </td>
                            <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">
                                {{ $u->mahasiswa->prodi->nama ?? '-' }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex px-2.5 py-1 text-[10px] font-bold bg-[#234C6A]/10 text-[#234C6A] dark:bg-blue-900/40 dark:text-blue-300 rounded uppercase tracking-wider">
                                    {{ $u->jenis_ujian }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-sm text-siakad-dark dark:text-gray-200">
                                @if($u->tanggal_ujian)
                                    <div class="font-semibold">{{ $u->tanggal_ujian->format('d M Y') }}</div>
                                    <div class="text-xs text-siakad-secondary dark:text-gray-400">Pukul: {{ substr($u->jam_mulai, 0, 5) }} - {{ substr($u->jam_selesai, 0, 5) }} WIB</div>
                                @else
                                    <span class="text-xs italic text-amber-600">Belum dijadwalkan</span>
                                @endif
                            </td>
                            <td class="py-4 px-5">
                                @if($u->status === 'approved')
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400 rounded-full">DISETUJUI</span>
                                @elseif($u->status === 'rejected')
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-400 rounded-full">REVISI/TOLAK</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400 rounded-full">PENDING</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                <a href="{{ route('admin.pendaftaran-ujian.show', $u) }}" class="btn-primary-saas px-3 py-1 rounded text-xs font-semibold shadow hover:bg-siakad-dark transition">
                                    Verifikasi & Jadwalkan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-siakad-secondary dark:text-gray-400 bg-siakad-light/10">Tidak ada pengajuan pendaftaran ujian yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ujianList->hasPages())
            <div class="px-5 py-4 border-t border-siakad-light dark:border-gray-700">
                {{ $ujianList->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
