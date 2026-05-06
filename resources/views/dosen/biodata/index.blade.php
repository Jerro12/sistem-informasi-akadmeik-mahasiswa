<x-app-layout>
    <x-slot name="header">
        Biodata Dosen
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1 flex flex-col gap-6">
            <div class="card-saas p-6">
                <div class="text-center mb-6">
                    @if($dosen->foto)
                        <img src="{{ asset('storage/' . $dosen->foto) }}" class="w-24 h-24 mx-auto rounded-xl object-cover mb-4 shadow-lg border-2 border-siakad-primary/20">
                    @else
                        <div class="w-24 h-24 mx-auto rounded-xl bg-siakad-primary flex items-center justify-center text-white text-4xl font-bold mb-4 shadow-lg">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <h2 class="text-xl font-semibold text-siakad-dark">{{ $user->name }}</h2>
                    <p class="text-sm font-mono text-siakad-secondary">{{ $dosen->nidn }}</p>
                    <span class="inline-flex mt-2 px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
                        Dosen Aktif
                    </span>
                </div>

                <div class="space-y-3 pt-4 border-t border-siakad-light">
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span class="text-siakad-dark">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 text-siakad-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        <span class="text-siakad-dark">{{ $dosen->prodi->nama ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Academic Info Dosen -->
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark mb-4 text-center">Informasi Akademik</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col items-center justify-center p-4 bg-siakad-primary/10 rounded-xl">
                        <p class="text-2xl font-bold text-siakad-primary">{{ $dosen->kelas->count() }}</p>
                        <p class="text-xs text-siakad-secondary mt-1">Kelas Diampu</p>
                    </div>
                    <div class="flex flex-col items-center justify-center p-4 bg-siakad-primary/10 rounded-xl">
                        <p class="text-2xl font-bold text-siakad-primary">{{ $dosen->mahasiswaBimbingan->count() }}</p>
                        <p class="text-xs text-siakad-secondary mt-1">Mhs Bimbingan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Update Profile -->
            <div class="card-saas">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Informasi Pribadi & Akun</h3>
                    <p class="text-xs text-siakad-secondary mt-1">Lengkapi data diri Anda sebagai tenaga pengajar</p>
                </div>
                <form action="{{ route('dosen.biodata.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">NIDN</label>
                                <input type="text" value="{{ $dosen->nidn }}" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card); opacity: 0.6;" readonly disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">No. Handphone</label>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $dosen->no_hp) }}" placeholder="Contoh: 08123456789" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);">
                                    <option value="" disabled {{ !$dosen->jenis_kelamin ? 'selected' : '' }}>-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $dosen->tempat_lahir) }}" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $dosen->tanggal_lahir) }}" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Foto Profil</label>
                                <input type="file" name="foto" class="input-saas w-full px-4 py-2 text-sm" style="background-color: var(--bg-card);">
                                <p class="text-[10px] text-siakad-secondary mt-1">Format: JPG, PNG. Max: 2MB</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-siakad-dark mb-2">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);">{{ old('alamat', $dosen->alamat) }}</textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-siakad-light flex justify-end">
                        <button type="submit" class="btn-primary-saas px-5 py-2.5 rounded-lg text-sm font-medium">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card-saas">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Ubah Password</h3>
                    <p class="text-xs text-siakad-secondary mt-1">Pastikan menggunakan password yang kuat</p>
                </div>
                <form action="{{ route('dosen.biodata.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-siakad-dark mb-2">Password Lama</label>
                            <input type="password" name="current_password" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Password Baru</label>
                                <input type="password" name="password" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);" required minlength="8">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-siakad-dark mb-2">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="input-saas w-full px-4 py-2.5 text-sm" style="background-color: var(--bg-card);" required>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-siakad-light flex justify-end">
                        <button type="submit" class="btn-primary-saas px-5 py-2.5 rounded-lg text-sm font-medium">
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
