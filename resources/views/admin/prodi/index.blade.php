<x-app-layout>
    <x-slot name="header">
        Data Program Studi
    </x-slot>

<div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
        <p class="text-sm text-siakad-secondary dark:text-gray-400 hidden md:block">
            Kelola data program studi berdasarkan fakultas
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
        Tambah Prodi
    </button>
</div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-400 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Prodi Grouped by Fakultas (Collapsible) -->
    @foreach($fakultas as $index => $f)
    <div class="card-saas overflow-hidden mb-4 dark:bg-gray-800" x-data="{ open: true }">
        <!-- Fakultas Header (Clickable) -->
        <button @click="open = !open" type="button" class="w-full px-6 py-4 bg-siakad-primary/5 border-b border-siakad-light dark:bg-gray-700/50 dark:border-gray-700 flex items-center justify-between hover:bg-siakad-primary/10 dark:hover:bg-gray-700 transition cursor-pointer text-left">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="font-semibold text-siakad-dark dark:text-white">{{ $f->nama }}</h3>
                    <p class="text-xs text-siakad-secondary dark:text-gray-400">{{ $f->prodi->count() }} Program Studi</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center px-3 py-1 text-xs font-medium bg-siakad-primary/10 text-siakad-primary dark:bg-indigo-900/50 dark:text-indigo-400 rounded-full">
                    {{ $f->prodi->sum(fn($p) => $p->mahasiswa_count ?? 0) }} Mahasiswa
                </span>
                <!-- Chevron Icon -->
                <svg class="w-5 h-5 text-siakad-secondary dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        <!-- Prodi Table (Collapsible Content) -->
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="overflow-x-auto">
            <!-- Table (Desktop) -->
            <table class="hidden md:table w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-16">#</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Nama Program Studi</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-32">Mahasiswa</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-24">Dosen</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($f->prodi as $idx => $p)
                    <tr class="border-b border-siakad-light/50 dark:border-gray-700/50 hover:bg-siakad-light/10 dark:hover:bg-gray-700/30 transition">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $idx + 1 }}</td>
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-siakad-dark dark:text-white">{{ $p->nama }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-primary/10 text-siakad-primary dark:bg-blue-900/50 dark:text-blue-400 rounded-full">{{ $p->mahasiswa_count ?? 0 }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-secondary/10 text-siakad-secondary dark:bg-gray-700 dark:text-gray-300 rounded-full">{{ $p->dosen_count ?? 0 }}</span>
                        </td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="editProdi({{ $p->id }}, '{{ addslashes($p->nama) }}', {{ $f->id }})" class="p-2 text-siakad-secondary dark:text-gray-400 hover:text-siakad-primary dark:hover:text-blue-400 hover:bg-siakad-primary/10 dark:hover:bg-gray-700 rounded-lg transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button onclick="confirmDeleteProdi({{ $p->id }}, '{{ addslashes($p->nama) }}', {{ $p->mahasiswa_count ?? 0 }}, {{ $p->dosen_count ?? 0 }})" class="p-2 text-siakad-secondary dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-siakad-secondary dark:text-gray-400 text-sm">
                            Belum ada program studi di fakultas ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @if($fakultas->isEmpty())
    <div class="card-saas p-12 text-center dark:bg-gray-800">
        <div class="w-16 h-16 bg-siakad-light/50 dark:bg-gray-700/50 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <p class="text-siakad-dark dark:text-white font-medium mb-1">Belum ada data fakultas</p>
        <p class="text-siakad-secondary dark:text-gray-400 text-sm">Tambahkan fakultas terlebih dahulu sebelum membuat program studi</p>
    </div>
    @endif

    <!-- Create Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Tambah Program Studi</h3>
            </div>
            <form action="{{ route('admin.prodi.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    @if(auth()->user()->isSuperAdmin())
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Fakultas</label>
                        <select name="fakultas_id" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            <option value="">Pilih Fakultas</option>
                            @foreach($fakultas as $f)
                            <option value="{{ $f->id }}">{{ $f->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="fakultas_id" value="{{ auth()->user()->fakultas_id }}">
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Prodi</label>
                        <input type="text" name="nama" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Masukkan nama prodi" required>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md animate-fade-in">
            <div class="px-6 py-4 border-b border-siakad-light dark:border-gray-700">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white">Edit Program Studi</h3>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="p-6 space-y-4">
                    @if(auth()->user()->isSuperAdmin())
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Fakultas</label>
                        <select name="fakultas_id" id="editFakultas" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                            @foreach($fakultas as $f)
                            <option value="{{ $f->id }}">{{ $f->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="fakultas_id" id="editFakultas" value="{{ auth()->user()->fakultas_id }}">
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-siakad-dark dark:text-gray-300 mb-2">Nama Prodi</label>
                        <input type="text" name="nama" id="editNama" class="input-saas w-full px-4 py-2.5 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" required>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-siakad-light dark:border-gray-700 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                    <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Warning Delete Modal -->
    <div id="deleteWarningModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Peringatan Hapus Program Studi!</h3>
                <p id="deleteWarningText" class="text-sm text-gray-600 dark:text-gray-300 mb-6"></p>
                
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" onclick="document.getElementById('deleteWarningModal').classList.add('hidden')" class="btn-ghost-saas px-5 py-2.5 rounded-lg text-sm font-medium dark:text-white">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition shadow-md">Tetap Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editProdi(id, nama, fakultasId) {
            document.getElementById('editForm').action = `/admin/prodi/${id}`;
            document.getElementById('editNama').value = nama;
            if (document.getElementById('editFakultas')) {
                document.getElementById('editFakultas').value = fakultasId;
            }
            document.getElementById('editModal').classList.remove('hidden');
        }

        function confirmDeleteProdi(id, nama, mahasiswaCount, dosenCount) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/admin/prodi/${id}`;
            
            const warningText = document.getElementById('deleteWarningText');
            if (mahasiswaCount > 0 || dosenCount > 0) {
                warningText.innerHTML = `⚠️ <strong class="text-red-600">WARNING:</strong> Program Studi <strong>${nama}</strong> masih memiliki data terkait (<strong>${mahasiswaCount} Mahasiswa</strong>, <strong>${dosenCount} Dosen</strong>). Menghapus prodi ini akan berdampak pada data yang terhubung!`;
            } else {
                warningText.innerHTML = `Apakah Anda yakin ingin menghapus Program Studi <strong>${nama}</strong>?`;
            }
            document.getElementById('deleteWarningModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
