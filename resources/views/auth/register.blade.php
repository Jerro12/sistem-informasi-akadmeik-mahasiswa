<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Create New Account</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please complete your identity to join us.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ role: 'mahasiswa' }">
        @csrf

        <!-- Role Selection -->
        <div class="space-y-2">
            <x-input-label :value="__('I am registering as')" class="text-xs font-bold uppercase tracking-wider text-gray-400" />
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex cursor-pointer rounded-xl border p-3 focus:outline-none transition-all duration-200" :class="role === 'mahasiswa' ? 'border-siakad-primary bg-siakad-primary/5 ring-1 ring-siakad-primary' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'">
                    <input type="radio" name="role" value="mahasiswa" class="sr-only" @click="role = 'mahasiswa'" checked>
                    <div class="flex items-center justify-center w-full gap-2">
                        <span class="text-sm font-bold" :class="role === 'mahasiswa' ? 'text-siakad-primary' : 'text-gray-500 dark:text-gray-400'">Mahasiswa</span>
                    </div>
                </label>
                <label class="relative flex cursor-pointer rounded-xl border p-3 focus:outline-none transition-all duration-200" :class="role === 'dosen' ? 'border-siakad-primary bg-siakad-primary/5 ring-1 ring-siakad-primary' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'">
                    <input type="radio" name="role" value="dosen" class="sr-only" @click="role = 'dosen'">
                    <div class="flex items-center justify-center w-full gap-2">
                        <span class="text-sm font-bold" :class="role === 'dosen' ? 'text-siakad-primary' : 'text-gray-500 dark:text-gray-400'">Dosen</span>
                    </div>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Name & Username Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div class="relative group">
                <x-input-label for="name" :value="__('Full Name')" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <input id="name" class="block w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm" type="text" name="name" :value="old('name')" required autofocus placeholder="Full Name" />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <!-- NIM / NIDN -->
            <div class="relative group">
                <x-input-label for="username" :value="__('Identity Number')" class="sr-only" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"/></svg>
                    </div>
                    <input id="username" class="block w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm" type="text" name="username" :value="old('username')" required :placeholder="role === 'mahasiswa' ? 'NIM' : 'NIDN'" />
                </div>
                <x-input-error :messages="$errors->get('username')" class="mt-1" />
            </div>
        </div>

        <!-- Prodi -->
        <div class="relative group">
            <x-input-label for="prodi_id" :value="__('Study Program')" class="sr-only" />
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <select id="prodi_id" name="prodi_id" class="block w-full pl-11 pr-10 py-3 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none appearance-none font-medium sm:text-sm" required>
                    <option value="" disabled selected>Select Study Program</option>
                    @foreach($prodis as $prodi)
                        <option value="{{ $prodi->id }}" {{ old('prodi_id') == $prodi->id ? 'selected' : '' }}>{{ $prodi->nama }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('prodi_id')" class="mt-1" />
        </div>

        <!-- Password Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Password -->
            <div class="relative group">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <input id="password" class="block w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm" type="password" name="password" required placeholder="Password" />
                </div>
            </div>
            <!-- Confirm -->
            <div class="relative group">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-siakad-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <input id="password_confirmation" class="block w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-siakad-primary focus:border-siakad-primary transition-all duration-200 outline-none placeholder-gray-400 dark:placeholder-gray-500 font-medium sm:text-sm" type="password" name="password_confirmation" required placeholder="Confirm Password" />
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 pt-4">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-siakad-primary hover:bg-siakad-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-siakad-primary transition-all duration-200 transform hover:-translate-y-0.5 hover:shadow-lg">
                {{ __('Register Now') }}
            </button>
            
            <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                Already have an account? 
                <a class="font-bold text-siakad-primary hover:underline transition-colors" href="{{ route('login') }}">
                    {{ __('Sign in here') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
