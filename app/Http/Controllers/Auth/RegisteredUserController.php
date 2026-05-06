<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $prodis = Prodi::all();
        return view('auth.register', compact('prodis'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:mahasiswa,dosen'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users'],
            'prodi_id' => ['required', 'exists:prodi,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->username . '@' . $request->role . '.siakad.com', 
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'password_plain' => $request->password,
            'role' => $request->role,
        ]);

        if ($request->role === 'mahasiswa') {
            Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $request->username,
                'prodi_id' => $request->prodi_id,
                'angkatan' => date('Y'),
                'status' => 'aktif',
            ]);
        } else {
            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $request->username,
                'prodi_id' => $request->prodi_id,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        $redirectRoute = match ($user->role) {
            'mahasiswa' => 'mahasiswa.dashboard',
            'dosen' => 'dosen.dashboard',
            default => 'dashboard',
        };

        return redirect(route($redirectRoute, absolute: false));
    }
}
