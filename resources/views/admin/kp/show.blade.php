<x-app-layout>
    <x-slot name="header">Detail KP</x-slot>
    <div class="mb-6"><a href="{{ route('admin.kp.index') }}" class="text-siakad-secondary hover:text-siakad-primary text-sm">← Kembali</a></div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card-saas p-6">
                <div class="flex items-start justify-between mb-4"><span class="px-3 py-1 text-xs font-medium rounded-full bg-{{ $kp->status_color }}-100 text-{{ $kp->status_color }}-700">{{ $kp->status_label }}</span><span class="text-2xl font-bold text-siakad-primary">{{ $kp->progress_percent }}%</span></div>
                <h2 class="text-lg font-bold text-siakad-dark">{{ $kp->nama_perusahaan }}</h2>
                <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                    <div class="p-3 rounded-lg bg-siakad-light/30"><p class="text-xs text-siakad-secondary">Mahasiswa</p><p class="font-medium text-siakad-dark">{{ $kp->mahasiswa->user->name }}</p><p class="text-xs text-siakad-secondary">{{ $kp->mahasiswa->nim }}</p></div>
                    <div class="p-3 rounded-lg bg-siakad-light/30"><p class="text-xs text-siakad-secondary">Periode</p><p class="font-medium text-siakad-dark">{{ $kp->tanggal_mulai->format('d M') }} - {{ $kp->tanggal_selesai->format('d M Y') }}</p></div>
                </div>
            </div>
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Assign Pembimbing</h3>
                <form action="{{ route('admin.kp.assign-pembimbing', $kp) }}" method="POST" class="flex items-end gap-3">@csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-siakad-dark mb-1">Pembimbing Kampus</label>
                        <select name="pembimbing_id" id="pembimbing_id" class="w-full text-sm select-dosen-ajax" required>
                            @if($kp->pembimbing_id)
                                <option value="{{ $kp->pembimbing_id }}" selected>{{ $kp->pembimbing->user->name }}</option>
                            @else
                                <option value="">Pilih Dosen</option>
                            @endif
                        </select>
                    </div>
                    <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Simpan</button>
                </form>
            </div>
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Update Status</h3>
                <form action="{{ route('admin.kp.update-status', $kp) }}" method="POST" class="space-y-4">@csrf @method('PUT')
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Status</label><select name="status" class="input-saas w-full text-sm">@foreach(\App\Models\KerjaPraktek::getStatusList() as $k => $v)<option value="{{ $k }}" {{ $kp->status === $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach</select></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nomor Surat (Opsional)</label><input type="text" name="nomor_surat" value="{{ $kp->nomor_surat }}" placeholder="Contoh: 127/II.3.AU/TE//2026" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Catatan</label><textarea name="catatan" rows="2" class="input-saas w-full text-sm">{{ $kp->catatan }}</textarea></div>
                    <button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Update</button>
                </form>
            </div>
            @if(in_array($kp->status, ['seminar', 'revisi']))
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4">Input Nilai</h3>
                <form action="{{ route('admin.kp.update-nilai', $kp) }}" method="POST" class="grid grid-cols-2 gap-4">@csrf @method('PUT')
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Perusahaan</label><input type="number" name="nilai_perusahaan" value="{{ $kp->nilai_perusahaan }}" step="0.01" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Pembimbing</label><input type="number" name="nilai_pembimbing" value="{{ $kp->nilai_pembimbing }}" step="0.01" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Seminar</label><input type="number" name="nilai_seminar" value="{{ $kp->nilai_seminar }}" step="0.01" class="input-saas w-full text-sm"></div>
                    <div><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Akhir *</label><input type="number" name="nilai_akhir" value="{{ $kp->nilai_akhir }}" step="0.01" class="input-saas w-full text-sm" required></div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-siakad-dark mb-1">Nilai Huruf *</label><select name="nilai_huruf" class="input-saas w-full text-sm" required>@foreach(['A','B+','B','C+','C','D','E'] as $h)<option value="{{ $h }}" {{ $kp->nilai_huruf === $h ? 'selected' : '' }}>{{ $h }}</option>@endforeach</select></div>
                    <div class="col-span-2"><button type="submit" class="btn-primary-saas px-4 py-2.5 rounded-lg text-sm font-medium">Simpan Nilai</button></div>
                </form>
            </div>
            @endif
        </div>
        <div class="space-y-6">
            <div class="card-saas p-5"><h3 class="font-semibold text-siakad-dark dark:text-white mb-3">Info Perusahaan</h3><div class="text-sm space-y-2"><p class="text-siakad-dark dark:text-gray-300">{{ $kp->nama_perusahaan }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->alamat_perusahaan }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">Bidang: {{ $kp->bidang_usaha ?? '-' }}</p></div></div>
            <div class="card-saas p-5"><h3 class="font-semibold text-siakad-dark dark:text-white mb-3">Pembimbing Lapangan</h3><p class="text-sm text-siakad-dark dark:text-gray-300">{{ $kp->nama_pembimbing_lapangan ?? '-' }}</p><p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $kp->jabatan_pembimbing_lapangan }}</p></div>
            <div class="card-saas p-5">
                <h3 class="font-semibold text-siakad-dark dark:text-white mb-3">HP Mahasiswa</h3>
                <p class="text-sm text-siakad-dark dark:text-gray-300">{{ $kp->no_hp_mahasiswa ?? '-' }}</p>
            </div>
            @if($kp->pembimbing_id)
            <div class="card-saas p-5">
                <h3 class="font-semibold text-siakad-dark mb-3">Surat Permohonan</h3>
                <a href="{{ route('admin.kp.surat-permohonan', $kp) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-siakad-primary text-white rounded-lg text-sm font-medium hover:bg-siakad-primary/90 transition w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Surat Permohonan
                </a>
            </div>
            @endif
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.select-dosen-ajax');
        selects.forEach(select => {
            new TomSelect(select, {
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                placeholder: 'Ketik nama dosen...',
                preload: true,
                load: function(query, callback) {
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
</x-app-layout>
