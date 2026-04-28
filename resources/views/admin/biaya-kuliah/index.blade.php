<x-app-layout>
    <x-slot name="header">
        Pengaturan Biaya Kuliah
    </x-slot>

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-sm text-siakad-secondary dark:text-gray-400">Atur nominal biaya KRS/SPP per Program Studi</p>
        <button onclick="openModal('createModal')" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah Biaya
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-400 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Semester</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Fakultas</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Program Studi</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Nominal Biaya</th>
                        <th class="text-right py-3 px-4 text-xs font-semibold text-siakad-secondary uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($biaya as $b)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="py-4 px-4 text-sm font-medium text-siakad-dark dark:text-white">
                                {{ $b->tahunAkademik->tahun }} {{ ucfirst($b->tahunAkademik->semester) }}
                            </td>
                            <td class="py-4 px-4 text-sm text-siakad-secondary">
                                {{ $b->prodi->fakultas->nama ?? '-' }}
                            </td>
                            <td class="py-4 px-4 text-sm text-siakad-secondary">
                                {{ $b->prodi->nama }}
                            </td>
                            <td class="py-4 px-4 text-sm font-bold text-siakad-primary dark:text-blue-400">
                                Rp {{ number_format($b->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right">
                                <form action="{{ route('admin.biaya-kuliah.destroy', $b) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data biaya ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-siakad-secondary italic">Belum ada pengaturan biaya kuliah</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($biaya->hasPages())<div class="mt-4">{{ $biaya->links() }}</div>@endif

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('createModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Tambah Pengaturan Biaya</h3>
                <form action="{{ route('admin.biaya-kuliah.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-siakad-secondary mb-1">Pilih Semester</label>
                        <select name="tahun_akademik_id" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @foreach($tahunAkademik as $ta)
                                <option value="{{ $ta->id }}" {{ $ta->is_active ? 'selected' : '' }}>{{ $ta->tahun }} {{ ucfirst($ta->semester) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-secondary mb-1">Pilih Program Studi</label>
                        <select name="prodi_id" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @foreach($prodi as $p)
                                <option value="{{ $p->id }}">{{ $p->fakultas->nama ?? '' }} - {{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-siakad-secondary mb-1">Nominal Biaya (Rp)</label>
                        <input type="number" name="nominal" placeholder="Contoh: 1500000" required class="input-saas w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button>
                        <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan Pengaturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
    </script>
</x-app-layout>
