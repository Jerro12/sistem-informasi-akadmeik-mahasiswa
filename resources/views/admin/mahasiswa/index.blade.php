<x-app-layout>
    <x-slot name="header">
        Data Mahasiswa
    </x-slot>

    @if(session('success'))<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm text-siakad-secondary dark:text-gray-400">Kelola data mahasiswa dalam sistem</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <button onclick="openCreateModal()" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Tambah
            </button>
        </div>
    </div>
    <div class="mb-6">
        <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIM..." class="input-saas px-4 py-2 text-sm w-full sm:w-64 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
            
            <select name="fakultas_id" id="filterFakultas" class="input-saas px-4 py-2 text-sm w-full sm:w-48 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <option value="">Fakultas</option>
                @foreach($fakultasList as $f)
                <option value="{{ $f->id }}" {{ request('fakultas_id') == $f->id ? 'selected' : '' }}>{{ $f->nama }}</option>
                @endforeach
            </select>

            <select name="prodi_id" id="filterProdi" class="input-saas px-4 py-2 text-sm w-full sm:w-48 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <option value="">Prodi</option>
                @foreach($prodiList as $p)
                <option value="{{ $p->id }}" data-fakultas-id="{{ $p->fakultas_id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                @endforeach
            </select>

            <select name="angkatan" class="input-saas px-4 py-2 text-sm w-full sm:w-32 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300">
                <option value="">Angkatan</option>
                @foreach($angkatanList as $angkatan)
                <option value="{{ $angkatan }}" {{ request('angkatan') == $angkatan ? 'selected' : '' }}>{{ $angkatan }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto">Filter</button>
            <a href="{{ route('admin.mahasiswa.export', request()->all()) }}" target="_blank" class="btn-ghost-saas px-4 py-2 dark:text-white rounded-lg text-sm font-medium w-full sm:w-auto text-center border border-siakad-light dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export
            </a>
            @if(request()->anyFilled(['search', 'fakultas_id', 'prodi_id', 'angkatan']))
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn-ghost-saas px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto text-center">Reset</a>
            @endif
        </form>
    </div>

    <!-- Script for Dynamic Dropdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fakultasSelect = document.getElementById('filterFakultas');
            const prodiSelect = document.getElementById('filterProdi');
            const prodiOptions = Array.from(prodiSelect.options);

            function updateProdiOptions() {
                const selectedFakultasId = fakultasSelect.value;
                const currentProdiValue = prodiSelect.value;
                let isCurrentProdiValid = false;

                // First option (Semua Prodi) always visible
                prodiSelect.innerHTML = '';
                prodiSelect.appendChild(prodiOptions[0]);

                prodiOptions.slice(1).forEach(option => {
                    if (!selectedFakultasId || option.dataset.fakultasId === selectedFakultasId) {
                        prodiSelect.appendChild(option);
                        if (option.value === currentProdiValue) {
                            isCurrentProdiValid = true;
                        }
                    }
                });

                // Reset prodi selection if current selection is no longer valid
                if (currentProdiValue && !isCurrentProdiValid) {
                    prodiSelect.value = '';
                } else {
                    prodiSelect.value = currentProdiValue;
                }
            }

            fakultasSelect.addEventListener('change', updateProdiOptions);
            
            // Initial run to set correct state on page load (if filtering is active)
            updateProdiOptions();
        });
    </script>

    <!-- Table Card (Desktop) -->
    <div class="hidden md:block card-saas overflow-hidden dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full table-saas">
                <thead>
                    <tr class="bg-siakad-light/30 dark:bg-gray-900 border-b border-siakad-light dark:border-gray-700">
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-16">#</th>
                        
                        <!-- Sortable: Mahasiswa (Name) -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.mahasiswa.index', array_merge(request()->all(), ['sort' => 'name', 'order' => request('sort') == 'name' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Mahasiswa
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'name' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'name' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'name' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: NIM -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.mahasiswa.index', array_merge(request()->all(), ['sort' => 'nim', 'order' => request('sort') == 'nim' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                NIM
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'nim' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'nim' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'nim' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Prodi -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.mahasiswa.index', array_merge(request()->all(), ['sort' => 'prodi', 'order' => request('sort') == 'prodi' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Prodi
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'prodi' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'prodi' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'prodi' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <!-- Sortable: Angkatan -->
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">
                            <a href="{{ route('admin.mahasiswa.index', array_merge(request()->all(), ['sort' => 'angkatan', 'order' => request('sort') == 'angkatan' && request('order') == 'asc' ? 'desc' : 'asc'])) }}" class="group flex items-center gap-1 hover:text-siakad-primary transition">
                                Angkatan
                                <span class="flex flex-col text-[10px] leading-none {{ request('sort') == 'angkatan' ? 'text-siakad-primary' : 'text-gray-300' }}">
                                    <i class="opacity-{{ request('sort') == 'angkatan' && request('order') == 'asc' ? '100' : '40' }}">▲</i>
                                    <i class="opacity-{{ request('sort') == 'angkatan' && request('order') == 'desc' ? '100' : '40' }}">▼</i>
                                </span>
                            </a>
                        </th>

                        <th class="py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider w-16 text-center">Smst</th>

                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">IPK</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Password</th>
                        <th class="text-left py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-right py-3 px-5 text-xs font-semibold text-siakad-secondary dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-siakad-light dark:divide-gray-700">
                    @forelse($mahasiswa as $index => $m)
                    <tr class="border-b border-siakad-light/50 dark:border-gray-700/50">
                        <td class="py-4 px-5 text-sm text-siakad-secondary dark:text-gray-400">{{ $mahasiswa->firstItem() + $index }}</td>
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-siakad-primary dark:bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                                    {{ strtoupper(substr($m->user->name ?? '-', 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-siakad-dark dark:text-white">{{ $m->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            <span class="text-sm font-mono text-siakad-secondary dark:text-gray-400">{{ $m->nim }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="text-sm text-siakad-secondary dark:text-gray-400">{{ $m->prodi->nama ?? '-' }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium bg-siakad-secondary/10 text-siakad-secondary dark:bg-gray-700 dark:text-gray-300 rounded-full">{{ $m->angkatan }}</span>
                        </td>
                        <td class="py-4 px-5 text-center text-sm font-semibold text-siakad-dark dark:text-white">
                            {{ $m->semester_sekarang }}
                        </td>
                        <td class="py-4 px-5">
                            <span class="text-sm font-semibold text-siakad-primary dark:text-blue-400">{{ number_format($m->ipk ?? 0, 2) }}</span>
                        </td>
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-2" x-data="{ show: false, pass: '{{ addslashes($m->user->password_plain ?? 'N/A') }}' }">
                                <span class="text-xs font-mono text-gray-500 dark:text-gray-400" x-text="show ? pass : '••••••••'"></span>
                                <button @click="show = !show" class="text-gray-400 hover:text-siakad-primary transition">
                                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.577-2.387M8 8.05A2.992 2.992 0 007.828 10.828l3.125 3.125a2.991 2.991 0 003.354-.055m1.515-2.074a2.992 2.992 0 00-.776-3.875" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" /></svg>
                                </button>
                            </div>
                        </td>
                        <td class="py-4 px-5">
                            @if($m->status === 'aktif')
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 rounded-full border dark:border-emerald-500/20">Aktif</span>
                            @elseif($m->status === 'cuti')
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 rounded-full border dark:border-amber-500/20">Cuti</span>
                            @else
                            <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-gray-300 rounded-full border dark:border-gray-500/20">{{ ucfirst($m->status ?? 'Aktif') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.mahasiswa.show', $m) }}" class="p-2 text-siakad-secondary hover:text-siakad-primary hover:bg-siakad-primary/10 rounded-lg transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <button onclick="openEditModal({{ json_encode(['id'=>$m->id,'name'=>$m->user->name,'nim'=>$m->nim,'prodi_id'=>$m->prodi_id,'angkatan'=>$m->angkatan,'semester_sekarang'=>$m->semester_sekarang,'dosen_pa_id'=>$m->dosen_pa_id,'status'=>$m->status,'kurikulum_id'=>$m->kurikulum_id]) }})" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </button>
                                <form action="{{ route('admin.mahasiswa.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('Yakin?')">@csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-siakad-light/50 dark:bg-gray-700/50 rounded-xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-siakad-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <p class="text-siakad-secondary dark:text-gray-400 text-sm">Tidak ada data mahasiswa</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card List -->
    <div class="md:hidden space-y-4">
        @forelse($mahasiswa as $m)
        <div class="card-saas p-4 dark:bg-gray-800">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-siakad-primary dark:bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr($m->user->name ?? '-', 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-siakad-dark dark:text-white">{{ $m->user->name ?? '-' }}</h4>
                        <p class="text-xs text-siakad-secondary dark:text-gray-400 font-mono">{{ $m->nim }}</p>
                    </div>
                </div>
                @if($m->status === 'aktif')
                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-700 rounded-full">Aktif</span>
                @elseif($m->status === 'cuti')
                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-700 rounded-full">Cuti</span>
                @else
                <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold bg-slate-100 text-slate-600 rounded-full">{{ ucfirst($m->status ?? 'Aktif') }}</span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm text-siakad-secondary dark:text-gray-400 mb-4">
                <div>
                    <span class="block text-xs text-gray-400">Prodi</span>
                    <span class="font-medium text-siakad-dark dark:text-gray-200">{{ $m->prodi->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400">Angkatan</span>
                    <span class="font-medium text-siakad-dark dark:text-gray-200">{{ $m->angkatan }}</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-400">IPK</span>
                    <span class="font-semibold text-siakad-primary dark:text-blue-400">{{ number_format($m->ipk ?? 0, 2) }}</span>
                </div>
            </div>

            <a href="{{ route('admin.mahasiswa.show', $m) }}" class="flex items-center justify-center w-full py-2 bg-siakad-light dark:bg-gray-700 text-siakad-dark dark:text-white font-medium rounded-lg hover:bg-gray-200 transition text-sm">
                Detail Mahasiswa
            </a>
        </div>
        @empty
        <div class="card-saas p-8 text-center">
            <p class="text-siakad-secondary dark:text-gray-400">Tidak ada data mahasiswa</p>
        </div>
        @endforelse
    </div>
        @if($mahasiswa->hasPages())
        <div class="px-5 py-4 border-t border-siakad-light dark:border-gray-700">
            {{ $mahasiswa->links() }}
        </div>
        @endif
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('createModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Tambah Mahasiswa</h3>
                <form action="{{ route('admin.mahasiswa.store') }}" method="POST" id="createForm" class="space-y-4">@csrf
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Nama</label><input type="text" name="name" required class="input-saas w-full dark:bg-gray-700" autocomplete="off"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Password</label><input type="password" name="password" required minlength="8" class="input-saas w-full dark:bg-gray-700" autocomplete="new-password"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">NIM</label><input type="text" name="nim" required class="input-saas w-full dark:bg-gray-700" autocomplete="off"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Prodi</label><select name="prodi_id" id="createProdiSelect" onchange="filterKurikulumKonsentrasiCreate()" required class="input-saas w-full dark:bg-gray-700"><option value="">-- Pilih --</option>@foreach($prodiList as $p)<option value="{{ $p->id }}">{{ $p->nama }}</option>@endforeach</select></div>
                    <div class="grid grid-cols-1 gap-4">
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Kurikulum</label><select name="kurikulum_id" id="createKurikulumSelect" class="input-saas w-full dark:bg-gray-700"><option value="">Bebas Kurikulum</option>@foreach($kurikulumList as $k)<option value="{{ $k->id }}" data-prodi="{{ $k->prodi_id }}">{{ $k->nama }}</option>@endforeach</select></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Angkatan</label><input type="number" name="angkatan" required value="{{ date('Y') }}" class="input-saas w-full dark:bg-gray-700"></div>
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Semester Sekarang</label><input type="number" name="semester_sekarang" required value="1" min="1" max="14" class="input-saas w-full dark:bg-gray-700"></div>
                    </div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Dosen PA</label><select name="dosen_pa_id" class="input-saas w-full dark:bg-gray-700"><option value="">-- Pilih --</option>@foreach($dosenList as $d)<option value="{{ $d->id }}">{{ $d->user->name }}</option>@endforeach</select></div>
                    <div class="flex justify-end gap-3"><button type="button" onclick="closeModal('createModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeModal('editModal')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-siakad-dark dark:text-white mb-4">Edit Mahasiswa</h3>
                <form id="editForm" method="POST" class="space-y-4">@csrf @method('PUT')
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Nama</label><input type="text" name="name" id="editName" required class="input-saas w-full dark:bg-gray-700" autocomplete="off"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Password (kosongkan jika tidak diubah)</label><input type="password" name="password" minlength="8" class="input-saas w-full dark:bg-gray-700" autocomplete="new-password"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">NIM</label><input type="text" name="nim" id="editNim" required class="input-saas w-full dark:bg-gray-700" autocomplete="off"></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Prodi</label><select name="prodi_id" id="editProdiId" onchange="filterKurikulumKonsentrasiEdit()" required class="input-saas w-full dark:bg-gray-700"><option value="">-- Pilih --</option>@foreach($prodiList as $p)<option value="{{ $p->id }}">{{ $p->nama }}</option>@endforeach</select></div>
                    <div class="grid grid-cols-1 gap-4">
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Kurikulum</label><select name="kurikulum_id" id="editKurikulumId" class="input-saas w-full dark:bg-gray-700"><option value="">Bebas Kurikulum</option>@foreach($kurikulumList as $k)<option value="{{ $k->id }}" data-prodi="{{ $k->prodi_id }}">{{ $k->nama }}</option>@endforeach</select></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Angkatan</label><input type="number" name="angkatan" id="editAngkatan" required class="input-saas w-full dark:bg-gray-700"></div>
                        <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Semester Sekarang</label><input type="number" name="semester_sekarang" id="editSemesterSekarang" required min="1" max="14" class="input-saas w-full dark:bg-gray-700"></div>
                    </div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Dosen PA</label><select name="dosen_pa_id" id="editDosenPaId" class="input-saas w-full dark:bg-gray-700"><option value="">-- Pilih --</option>@foreach($dosenList as $d)<option value="{{ $d->id }}">{{ $d->user->name }}</option>@endforeach</select></div>
                    <div><label class="block text-sm font-medium text-siakad-secondary mb-1">Status</label><select name="status" id="editStatus" required class="input-saas w-full dark:bg-gray-700"><option value="aktif">Aktif</option><option value="cuti">Cuti</option><option value="lulus">Lulus</option><option value="do">DO</option></select></div>
                    <div class="flex justify-end gap-3"><button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-sm text-siakad-secondary hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="btn-primary-saas px-4 py-2 rounded-lg text-sm">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        
        let createKurikulumData = [];
        let editKurikulumData = [];

        document.addEventListener('DOMContentLoaded', function() {
            const createKurikulumSelect = document.getElementById('createKurikulumSelect');
            if (createKurikulumSelect) {
                createKurikulumData = Array.from(createKurikulumSelect.options).map(opt => ({
                    value: opt.value,
                    text: opt.text,
                    prodiId: opt.getAttribute('data-prodi') || ''
                }));
            }
            const editKurikulumSelect = document.getElementById('editKurikulumId');
            if (editKurikulumSelect) {
                editKurikulumData = Array.from(editKurikulumSelect.options).map(opt => ({
                    value: opt.value,
                    text: opt.text,
                    prodiId: opt.getAttribute('data-prodi') || ''
                }));
            }
            
            // Trigger filters initially on load if values exist
            filterKurikulumKonsentrasiCreate(true);
            filterKurikulumKonsentrasiEdit(true);
        });

        function openCreateModal() {
            const createForm = document.getElementById('createForm');
            if (createForm) {
                createForm.reset();
            }
            filterKurikulumKonsentrasiCreate();
            openModal('createModal');
        }

        function filterKurikulumKonsentrasiCreate(isInitial = false) {
            const prodiSelect = document.getElementById('createProdiSelect');
            const kurikulumSelect = document.getElementById('createKurikulumSelect');
            if (!prodiSelect || !kurikulumSelect || createKurikulumData.length === 0) return;

            const prodiId = prodiSelect.value;
            const currentVal = kurikulumSelect.value;

            // Clear and add placeholder
            kurikulumSelect.innerHTML = '';
            kurikulumSelect.options[0] = new Option(createKurikulumData[0].text, createKurikulumData[0].value);

            let isValid = false;
            let index = 1;
            createKurikulumData.slice(1).forEach(data => {
                if (data.prodiId === prodiId) {
                    kurikulumSelect.options[index++] = new Option(data.text, data.value);
                    if (data.value === currentVal) isValid = true;
                }
            });

            if (isValid && !isInitial) {
                kurikulumSelect.value = currentVal;
            } else {
                kurikulumSelect.value = '';
            }
        }

        function filterKurikulumKonsentrasiEdit(isInitial = false) {
            const prodiSelect = document.getElementById('editProdiId');
            const kurikulumSelect = document.getElementById('editKurikulumId');
            if (!prodiSelect || !kurikulumSelect || editKurikulumData.length === 0) return;

            const prodiId = prodiSelect.value;
            const currentVal = kurikulumSelect.value;

            // Clear and add placeholder
            kurikulumSelect.innerHTML = '';
            kurikulumSelect.options[0] = new Option(editKurikulumData[0].text, editKurikulumData[0].value);

            let isValid = false;
            let index = 1;
            editKurikulumData.slice(1).forEach(data => {
                if (data.prodiId === prodiId) {
                    kurikulumSelect.options[index++] = new Option(data.text, data.value);
                    if (data.value === currentVal) isValid = true;
                }
            });

            if (isValid) {
                kurikulumSelect.value = currentVal;
            } else {
                kurikulumSelect.value = '';
            }
        }

        function openEditModal(m) {
            document.getElementById('editForm').action = `/admin/mahasiswa/${m.id}`;
            document.getElementById('editName').value = m.name;
            document.getElementById('editNim').value = m.nim;
            document.getElementById('editProdiId').value = m.prodi_id;
            
            // Set kurikulum value first so filter can validate it
            const editKurikulumSelect = document.getElementById('editKurikulumId');
            if (editKurikulumSelect) {
                editKurikulumSelect.value = m.kurikulum_id || '';
            }
            
            filterKurikulumKonsentrasiEdit();
            
            // Set again to be secure
            if (editKurikulumSelect) {
                editKurikulumSelect.value = m.kurikulum_id || '';
            }
            
            document.getElementById('editAngkatan').value = m.angkatan;
            document.getElementById('editSemesterSekarang').value = m.semester_sekarang || 1;
            document.getElementById('editDosenPaId').value = m.dosen_pa_id || '';
            document.getElementById('editStatus').value = m.status || 'aktif';
            openModal('editModal');
        }
    </script>
</x-app-layout>

