<x-app-layout>
    <x-slot name="header">Manajemen Pejabat</x-slot>

    <div class="space-y-8">
        {{-- Alert Messages --}}
        @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
        @endif

        {{-- Datalist for Dosen Search --}}
        <datalist id="dosenListOptions">
            @foreach($dosenList as $d)
                <option value="{{ $d->user->name ?? '' }}" data-nidn="{{ $d->nidn }}">{{ $d->user->name ?? '' }} (NIDN: {{ $d->nidn }})</option>
            @endforeach
        </datalist>

        {{-- Section: Pejabat Fakultas --}}
        <div>
            <h2 class="text-lg font-bold text-siakad-dark mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Pejabat Tingkat Fakultas
            </h2>
            <div class="grid grid-cols-1 gap-4">
                @foreach($fakultasList as $fak)
                <div class="card-saas overflow-hidden">
                    <div class="px-6 py-4 border-b border-siakad-light bg-siakad-light/30">
                        <h3 class="font-semibold text-siakad-dark">{{ str_starts_with(strtolower($fak->nama ?? ''), 'fakultas') ? $fak->nama : 'Fakultas ' . $fak->nama }}</h3>
                    </div>
                    <form action="{{ route('admin.pejabat.update-fakultas', $fak) }}" method="POST" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-xs font-medium text-siakad-dark mb-1">Nama Dekan (Ketik untuk mencari Dosen)</label>
                            <input type="text" name="nama_dekan" value="{{ $fak->nama_dekan }}" list="dosenListOptions" class="input-saas w-full text-sm" placeholder="Ketik nama Dosen Dekan...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-siakad-dark mb-1">Nama Wakil Dekan I (Ketik untuk mencari Dosen)</label>
                            <input type="text" name="nama_wakil_dekan1" value="{{ $fak->nama_wakil_dekan1 }}" list="dosenListOptions" class="input-saas w-full text-sm" placeholder="Ketik nama Dosen Wakil Dekan I...">
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" class="btn-primary-saas px-5 py-2.5 rounded-lg text-sm font-medium">Simpan Data Fakultas</button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Pejabat Program Studi --}}
        <div>
            <h2 class="text-lg font-bold text-siakad-dark mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-siakad-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 7l9-5-9-5-9 5 9 5z"></path></svg>
                Pejabat Tingkat Program Studi
            </h2>
            <div class="grid grid-cols-1 gap-4">
                @foreach($prodis as $prodi)
                <div class="card-saas overflow-hidden">
                    <div class="px-6 py-4 border-b border-siakad-light bg-siakad-light/30">
                        <h3 class="font-semibold text-siakad-dark">{{ $prodi->nama }}</h3>
                        <p class="text-xs text-siakad-secondary">Fakultas: {{ $prodi->fakultas->nama ?? '-' }}</p>
                    </div>
                    <form action="{{ route('admin.pejabat.update-prodi', $prodi) }}" method="POST" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-xs font-medium text-siakad-dark mb-1">Nama Ketua Program Studi (Cari Dosen)</label>
                            <input type="text" name="nama_ketua_prodi" id="kaprodi_nama_{{ $prodi->id }}" value="{{ $prodi->nama_ketua_prodi }}" list="dosenListOptions" onchange="autoFillNidn('kaprodi_nama_{{ $prodi->id }}', 'kaprodi_nidn_{{ $prodi->id }}')" class="input-saas w-full text-sm" placeholder="Ketik nama Ketua Prodi...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-siakad-dark mb-1">NIDN Ketua Program Studi</label>
                            <input type="text" name="nidn_ketua_prodi" id="kaprodi_nidn_{{ $prodi->id }}" value="{{ $prodi->nidn_ketua_prodi }}" class="input-saas w-full text-sm" placeholder="0000000000">
                        </div>
                        <div class="md:col-span-2 flex justify-end">
                            <button type="submit" class="btn-primary-saas px-5 py-2.5 rounded-lg text-sm font-medium">Simpan Data Prodi</button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Info Box --}}
        <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Catatan:</p>
                <p>Data pejabat yang diisi di sini akan otomatis muncul di dokumen resmi yang dicetak: <strong>KRS, KHS, Transkrip Nilai, Surat Permohonan KP,</strong> dan <strong>Jadwal Perkuliahan</strong>.</p>
            </div>
        </div>
    </div>

    <script>
        function autoFillNidn(namaInputId, nidnInputId) {
            const namaVal = document.getElementById(namaInputId).value;
            const datalist = document.getElementById('dosenListOptions');
            const options = datalist.options;
            
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === namaVal) {
                    const nidn = options[i].getAttribute('data-nidn');
                    if (nidn) {
                        document.getElementById(nidnInputId).value = nidn;
                    }
                    break;
                }
            }
        }
    </script>
</x-app-layout>
