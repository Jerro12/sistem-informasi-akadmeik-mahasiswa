<x-app-layout>
    <x-slot name="header">
        Persyaratan Dokumen Ujian
    </x-slot>

    <div class="card-saas p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-siakad-dark">Manajemen Persyaratan Dokumen Ujian</h3>
                <p class="text-sm text-siakad-secondary">Kelola daftar dokumen yang harus diunggah mahasiswa untuk mendaftar ujian.</p>
            </div>
            
            <button onclick="document.getElementById('modal-create-syarat').classList.remove('hidden')" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Syarat
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-siakad-light/50 border-y border-siakad-light">
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-dark uppercase tracking-wider">No</th>
                        @if(Auth::user()->role === 'superadmin')
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-dark uppercase tracking-wider">Prodi</th>
                        @endif
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-dark uppercase tracking-wider">Jenis Ujian</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-dark uppercase tracking-wider">Nama Persyaratan</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-dark uppercase tracking-wider">Wajib</th>
                        <th class="py-3 px-4 text-xs font-semibold text-siakad-dark uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light">
                    @forelse($syaratList as $syarat)
                    <tr class="hover:bg-siakad-light/20 transition-colors">
                        <td class="py-3 px-4 text-sm text-siakad-dark">{{ $loop->iteration }}</td>
                        @if(Auth::user()->role === 'superadmin')
                        <td class="py-3 px-4 text-sm text-siakad-dark">{{ $syarat->prodi->nama ?? '-' }}</td>
                        @endif
                        <td class="py-3 px-4 text-sm text-siakad-dark capitalize">{{ $syarat->jenis_ujian }}</td>
                        <td class="py-3 px-4 text-sm text-siakad-dark">{{ $syarat->nama_persyaratan }}</td>
                        <td class="py-3 px-4 text-sm text-siakad-dark">
                            @if($syarat->is_required)
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">Wajib</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">Opsional</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-sm text-center">
                            <form action="{{ route('admin.syarat-ujian.destroy', $syarat) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus persyaratan ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->role === 'superadmin' ? 6 : 5 }}" class="py-8 text-center text-siakad-secondary text-sm">
                            Belum ada persyaratan ujian yang ditambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create -->
    <div id="modal-create-syarat" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900">Tambah Persyaratan Ujian</h3>
                    <button type="button" onclick="document.getElementById('modal-create-syarat').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form action="{{ route('admin.syarat-ujian.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        @if(Auth::user()->role === 'superadmin' && !Auth::user()->prodi_id)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                            <select name="prodi_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-siakad-primary focus:ring focus:ring-siakad-primary/20 text-sm" required>
                                <option value="">Pilih Prodi</option>
                                @foreach($prodiList as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Ujian</label>
                            <select name="jenis_ujian" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-siakad-primary focus:ring focus:ring-siakad-primary/20 text-sm" required>
                                <option value="proposal">Seminar Proposal</option>
                                <option value="hasil">Seminar Hasil</option>
                                <option value="sidang">Sidang Skripsi</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Persyaratan</label>
                            <input type="text" name="nama_persyaratan" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-siakad-primary focus:ring focus:ring-siakad-primary/20 text-sm" placeholder="Contoh: Bukti Pembayaran SPP" required>
                        </div>

                        <div class="flex items-center mt-2">
                            <input type="checkbox" name="is_required" id="is_required" value="1" class="rounded border-gray-300 text-siakad-primary shadow-sm focus:border-siakad-primary focus:ring focus:ring-siakad-primary/20" checked>
                            <label for="is_required" class="ml-2 block text-sm text-gray-900">Wajib Diunggah</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('modal-create-syarat').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-siakad-primary rounded-lg hover:bg-siakad-primary/90 transition-colors">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
