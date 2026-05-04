<x-app-layout>
    <x-slot name="header">
        Data Konsentrasi
    </x-slot>

<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 hidden md:block">
            Kelola data konsentrasi / peminatan berdasarkan program studi
        </p>
    </div>

    <button
        onclick="document.getElementById('createModal').classList.remove('hidden')"
        class="btn-primary-saas w-full md:w-auto px-4 py-2.5 rounded-lg text-sm font-medium flex items-center justify-center gap-2"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Tambah Konsentrasi
    </button>
</div>

    <!-- Grouped by Prodi -->
    @foreach($prodis as $index => $p)
    <div class="card-saas overflow-hidden mb-4 dark:bg-gray-800" x-data="{ open: true }">
        <button @click="open = !open" type="button" class="w-full px-6 py-4 bg-siakad-primary/5 border-b border-siakad-light dark:bg-gray-700/50 dark:border-gray-700 flex items-center justify-between hover:bg-siakad-primary/10 dark:hover:bg-gray-700 transition cursor-pointer text-left">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="font-semibold text-siakad-dark dark:text-white">{{ $p->nama }}</h3>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        <div x-show="open" class="overflow-x-auto">
            <table class="hidden md:table w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-16">#</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nama Konsentrasi</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $konsentrasis = $konsentrasi->where('prodi_id', $p->id); @endphp
                    @forelse($konsentrasis as $idx => $k)
                    <tr class="border-b border-siakad-light/50 dark:border-gray-700/50 hover:bg-siakad-light/10 dark:hover:bg-gray-700/30 transition">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $loop->iteration }}</td>
                        <td class="py-4 px-5 text-sm font-medium text-siakad-dark dark:text-white">{{ $k->nama_konsentrasi }}</td>
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $k->kode_konsentrasi ?? '-' }}</td>
                        <td class="py-4 px-5">
                            @if($k->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aktif</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="editKonsentrasi({{ $k->id }}, '{{ $k->nama_konsentrasi }}', '{{ $k->kode_konsentrasi }}', {{ $k->is_active ? 'true' : 'false' }}, {{ $p->id }})" class="p-2 text-siakad-secondary dark:text-gray-400 hover:text-siakad-primary dark:hover:text-blue-400 hover:bg-siakad-primary/10 dark:hover:bg-gray-700 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('admin.konsentrasi.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus konsentrasi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-siakad-secondary dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-siakad-secondary dark:text-gray-400 text-sm">
                            Belum ada konsentrasi di program studi ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if($prodis->isEmpty())
    <div class="card-saas p-12 text-center dark:bg-gray-800">
        <p class="text-siakad-dark dark:text-white font-medium mb-1">Belum ada data Program Studi</p>
    </div>
    @endif

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/40 z-50 p-4">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tambah Konsentrasi</h3>
            </div>
            <form action="{{ route('admin.konsentrasi.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Program Studi</label>
                        <select name="prodi_id" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            <option value="">Pilih Program Studi</option>
                            @foreach($prodis as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Konsentrasi</label>
                        <input type="text" name="nama_konsentrasi" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: Teknik Perangkat Lunak" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kode Konsentrasi (Opsional)</label>
                        <input type="text" name="kode_konsentrasi" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: RPL">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-siakad-primary focus:ring-siakad-primary">
                            <span class="text-sm text-siakad-dark dark:text-gray-300">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/40 z-50 p-4">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Edit Konsentrasi</h3>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Program Studi</label>
                        <select name="prodi_id" id="editProdi" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            @foreach($prodis as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Konsentrasi</label>
                        <input type="text" name="nama_konsentrasi" id="editNama" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Kode Konsentrasi (Opsional)</label>
                        <input type="text" name="kode_konsentrasi" id="editKode" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="editActive" value="1" class="rounded border-gray-300 text-siakad-primary focus:ring-siakad-primary">
                            <span class="text-sm text-siakad-dark dark:text-gray-300">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script>
        function editKonsentrasi(id, nama, kode, active, prodiId) {
            document.getElementById('editForm').action = `/admin/master-data/konsentrasi/${id}`;
            document.getElementById('editNama').value = nama;
            document.getElementById('editKode').value = kode !== '-' ? kode : '';
            document.getElementById('editProdi').value = prodiId;
            document.getElementById('editActive').checked = active;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
