<?php
 
 namespace App\Http\Controllers\Dosen;
 
 use App\Http\Controllers\Controller;
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
 use Illuminate\Support\Facades\Hash;
 
 class BiodataController extends Controller
 {
     public function index()
     {
         $user = Auth::user();
         $dosen = $user->dosen;
         
         if (!$dosen) {
             abort(403, 'Unauthorized');
         }
 
         $dosen->load(['prodi.fakultas']);
 
         return view('dosen.biodata.index', compact('user', 'dosen'));
     }
 
     public function update(Request $request)
     {
         $user = Auth::user();
         $dosen = $user->dosen;
 
         if (!$dosen) {
             abort(403, 'Unauthorized');
         }
 
         $validated = $request->validate([
             'name' => 'required|string|max:255',
             'email' => 'required|email|unique:users,email,' . $user->id,
             'no_hp' => 'nullable|string|max:20',
             'jenis_kelamin' => 'nullable|in:L,P',
             'tempat_lahir' => 'nullable|string|max:100',
             'tanggal_lahir' => 'nullable|date',
             'alamat' => 'nullable|string|max:500',
             'provinsi' => 'nullable|string|max:100',
             'kecamatan' => 'nullable|string|max:100',
             'kelurahan' => 'nullable|string|max:100',
             'desa' => 'nullable|string|max:100',
             'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
         ]);
 
         // Update user
         $user->update([
             'name' => $validated['name'],
             'email' => $validated['email'],
         ]);
 
         // Update dosen
         $dosenData = [
             'no_hp' => $validated['no_hp'],
             'jenis_kelamin' => $validated['jenis_kelamin'],
             'tempat_lahir' => $validated['tempat_lahir'],
             'tanggal_lahir' => $validated['tanggal_lahir'],
             'alamat' => $validated['alamat'],
             'provinsi' => $validated['provinsi'],
             'kecamatan' => $validated['kecamatan'],
             'kelurahan' => $validated['kelurahan'],
             'desa' => $validated['desa'],
         ];
 
         if ($request->hasFile('foto')) {
             $path = $request->file('foto')->store('profile_photos', 'public');
             $dosenData['foto'] = $path;
         }
 
         $dosen->update($dosenData);
 
         return redirect()->back()->with('success', 'Biodata berhasil diperbarui');
     }
 
     public function updatePassword(Request $request)
     {
         $request->validate([
             'current_password' => 'required',
             'password' => 'required|min:8|confirmed',
         ]);
 
         $user = Auth::user();
 
         if (!Hash::check($request->current_password, $user->password)) {
             return redirect()->back()->with('error', 'Password lama tidak sesuai');
         }
 
         $user->update([
             'password' => Hash::make($request->password),
             'password_plain' => $request->password,
         ]);
 
         return redirect()->back()->with('success', 'Password berhasil diperbarui');
     }
 }
