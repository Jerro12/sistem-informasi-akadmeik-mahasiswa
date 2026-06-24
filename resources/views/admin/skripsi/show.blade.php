<x-app-layout>
    <x-slot name="header">
        Detail Skripsi
    </x-slot>

    <div class="mb-6">
        <a href="{{ route('admin.skripsi.index') }}" class="inline-flex items-center gap-2 text-siakad-secondary hover:text-siakad-primary transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Header -->
            <div class="card-saas p-6">
                <div class="flex items-start justify-between mb-4">
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $skripsi->status_color }}-100 text-{{ $skripsi->status_color }}-700">{{ $skripsi->status_label }}</span>
                    <span class="text-2xl font-bold text-siakad-primary">{{ $skripsi->progress_percent }}%</span>
                </div>
                <h2 class="text-lg font-bold text-siakad-dark">{{ $skripsi->judul }}</h2>
                @if($skripsi->abstrak)
                <p class="text-sm text-siakad-secondary mt-3">{{ $skripsi->abstrak }}</p>
                @endif

                <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="p-3 rounded-lg bg-siakad-light/30">
                        <p class="text-xs text-siakad-secondary">Mahasiswa</p>
                        <p class="font-medium text-siakad-dark">{{ $skripsi->mahasiswa->user->name }}</p>
                        <p class="text-xs text-siakad-secondary">{{ $skripsi->mahasiswa->nim }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-siakad-light/30">
                        <p class="text-xs text-siakad-secondary">Bidang</p>
                        <p class="font-medium text-siakad-dark">{{ $skripsi->bidang_kajian ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Assign Pembimbing -->
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Pembimbing</h3>
                <form action="{{ route('admin.skripsi.assign-pembimbing', $skripsi) }}" method="POST" class="grid grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Pembimbing 1 *</label>
                        <select name="pembimbing1_id" id="pembimbing1" class="w-full text-sm select-dosen-ajax" required>
                            @if($skripsi->pembimbing1_id)
                                <option value="{{ $skripsi->pembimbing1_id }}" selected>{{ $skripsi->pembimbing1->user->name }}</option>
                            @else
                                <option value="">Pilih Dosen</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Pembimbing 2</label>
                        <select name="pembimbing2_id" id="pembimbing2" class="w-full text-sm select-dosen-ajax">
                            @if($skripsi->pembimbing2_id)
                                <option value="{{ $skripsi->pembimbing2_id }}" selected>{{ $skripsi->pembimbing2->user->name }}</option>
                            @else
                                <option value="">Tidak ada</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-span-2">
                        <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Simpan Pembimbing</button>
                    </div>
                </form>
            </div>

            <!-- Update Status -->
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Update Status</h3>
                <form action="{{ route('admin.skripsi.update-status', $skripsi) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Status</label>
                        <select name="status" class="input-saas w-full text-sm">
                            @foreach(\App\Models\Skripsi::getStatusList() as $key => $label)
                            <option value="{{ $key }}" {{ $skripsi->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Catatan (opsional)</label>
                        <textarea name="catatan_admin" rows="2" class="input-saas w-full text-sm">{{ $skripsi->catatan_admin }}</textarea>
                    </div>
                    <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Update Status</button>
                </form>
            </div>

            <!-- Input Nilai -->
            @if($skripsi->status === 'sidang' || $skripsi->status === 'revisi')
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Input Nilai Akhir</h3>
                <form action="{{ route('admin.skripsi.update-nilai', $skripsi) }}" method="POST" class="grid grid-cols-2 gap-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Angka</label>
                        <input type="number" name="nilai_akhir" value="{{ $skripsi->nilai_akhir }}" step="0.01" min="0" max="100" class="input-saas w-full text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Huruf</label>
                        <select name="nilai_huruf" class="input-saas w-full text-sm" required>
                            @foreach(['A', 'B+', 'B', 'C+', 'C', 'D', 'E'] as $huruf)
                            <option value="{{ $huruf }}" {{ $skripsi->nilai_huruf === $huruf ? 'selected' : '' }}>{{ $huruf }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Simpan & Selesaikan</button>
                    </div>
                </form>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Timeline -->
            <div class="card-saas p-5">
                <h3 class="font-semibold text-siakad-dark mb-4">Timeline</h3>
                <div class="space-y-3 text-sm">
                    @php
                        $milestones = [
                            ['date' => $skripsi->tanggal_pengajuan, 'label' => 'Pengajuan'],
                            ['date' => $skripsi->tanggal_acc_judul, 'label' => 'ACC Judul'],
                            ['date' => $skripsi->tanggal_seminar_proposal, 'label' => 'Sempro'],
                            ['date' => $skripsi->tanggal_seminar_hasil, 'label' => 'Semhas'],
                            ['date' => $skripsi->tanggal_sidang, 'label' => 'Sidang'],
                            ['date' => $skripsi->tanggal_selesai, 'label' => 'Selesai'],
                        ];
                    @endphp
                    @foreach($milestones as $m)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $m['date'] ? 'bg-emerald-500' : 'bg-siakad-light' }}"></div>
                        <span class="{{ $m['date'] ? 'text-siakad-dark' : 'text-siakad-secondary' }}">{{ $m['label'] }}</span>
                        <span class="ml-auto text-siakad-secondary">{{ $m['date']?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Bimbingan Summary -->
            <div class="card-saas p-5">
                <h3 class="font-semibold text-siakad-dark mb-4">Riwayat Bimbingan</h3>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-2xl font-bold text-siakad-primary">{{ $skripsi->bimbingan->count() }}</p>
                        <p class="text-sm text-siakad-secondary">Total Pertemuan</p>
                    </div>
                </div>

                <!-- Bimbingan Details -->
                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-1 border-t border-siakad-light/50 pt-4">
                    <h4 class="text-xs font-bold text-siakad-dark uppercase tracking-wider mb-2">Log Bimbingan</h4>
                    @forelse($skripsi->bimbingan as $index => $b)
                        <div class="p-3 bg-siakad-light/20 rounded-lg border border-siakad-light/50 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold bg-[#234C6A]/10 text-[#234C6A] px-2 py-0.5 rounded">
                                    Pertemuan #{{ $index + 1 }}
                                </span>
                                <span class="text-[10px] text-siakad-secondary font-medium">{{ $b->tanggal_bimbingan->format('d M Y') }}</span>
                            </div>
                            <div>
                                <p class="text-[10px] text-siakad-dark font-bold">Catatan Mahasiswa:</p>
                                <p class="text-[11px] text-siakad-secondary leading-relaxed">{{ $b->catatan_mahasiswa }}</p>
                            </div>
                            
                            @if($b->catatan_dosen)
                                <div class="border-t border-siakad-light/50 pt-1.5">
                                    <p class="text-[10px] text-siakad-dark font-bold">Feedback Pembimbing:</p>
                                    <p class="text-[11px] text-siakad-secondary leading-relaxed">{{ $b->catatan_dosen }}</p>
                                </div>
                            @endif

                            @if($b->file_dokumen)
                                <div class="text-right pt-1">
                                    <a href="{{ route('bimbingan.download', $b) }}" target="_blank" class="text-[10px] text-siakad-primary hover:underline font-semibold inline-flex items-center gap-0.5">
                                        📄 Unduh Berkas
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-[11px] text-siakad-secondary text-center py-4">Belum ada riwayat bimbingan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.select-dosen-ajax');
        selects.forEach(select => {
            new TomSelect(select, {
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                placeholder: select.getAttribute('id') === 'pembimbing2' ? 'Pilih Dosen (Opsional)...' : 'Ketik nama dosen...',
                load: function(query, callback) {
                    if (!query.length) return callback();
                    fetch(`{{ route('admin.dosen.search') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(json => {
                            callback(json.map(item => ({
                                id: item.id,
                                name: item.name + (item.nidn ? ` (NIDN: ${item.nidn})` : '')
                            })));
                        }).catch(()=>{
                            callback();
                        });
                },
                render: {
                    option: function(item, escape) {
                        return `<div class="py-2 px-3">
                            <div class="font-medium text-sm text-gray-800 dark:text-white">${escape(item.name)}</div>
                        </div>`;
                    },
                    item: function(item, escape) {
                        return `<div class="font-medium text-sm text-gray-800 dark:text-white">${escape(item.name)}</div>`;
                    }
                }
            });
        });
    });
</script>
@endpush
