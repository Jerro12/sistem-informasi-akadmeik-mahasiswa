# Dokumentasi Hasil Revisi & Perubahan SIAKAD

Dokumentasi ini merangkum seluruh perubahan dan fitur baru yang telah diimplementasikan dalam fase revisi final.

## 1. Sistem Autentikasi (Hybrid Login)
Sistem sekarang mendukung login ganda untuk memudahkan berbagai role pengguna.
- **Login Hybrid**: User bisa masuk menggunakan **NIM / NIDN** (Mahasiswa/Dosen) atau **Email** (Admin/Superadmin).
- **Username System**: Implementasi kolom `username` di database sebagai identitas utama selain email.
- **Role-Based Email**: Generate email otomatis sekarang menggunakan format `identitas@role.siakad.com` untuk menjamin keunikan data dan mencegah bentrok antar role.
- **Password Transparency**: Admin dapat melihat password asli user melalui fitur `password_plain` dengan enkripsi visual (ikon mata) di dashboard.

## 2. Manajemen Data Admin
Pembersihan antarmuka untuk fokus pada data yang relevan.
- **Form & Tabel Bersih**: Menghapus input Email dan Konsentrasi dari form Mahasiswa/Dosen karena sudah diotomatisasi.
- **Visibilitas Password**: Kolom password ditambahkan di tabel user khusus untuk akun Admin.
- **Fix Operasional**: Perbaikan bug pada modal edit Mahasiswa dan sinkronisasi data user.

## 3. Akun Pribadi (Update Biodata)
Setiap user (Mahasiswa & Dosen) sekarang memiliki kontrol penuh atas data pribadi mereka.
- **Biodata Lengkap**: Form mencakup Nama, No. HP, Jenis Kelamin, Tempat/Tgl Lahir, Alamat, dan Foto Profil.
- **Mandiri**: User bisa mengupdate data diri dan mengganti password mereka sendiri.
- **Sidebar Integration**: Menu Biodata sudah terintegrasi di navigasi Mahasiswa dan Dosen.

## 4. Sistem Akademik & KRS
Peningkatan logika bisnis untuk akurasi data akademik.
- **Logika Konsentrasi Baru**: Pilihan konsentrasi dipindahkan dari profil Mahasiswa ke dalam **Form KRS**. Dropdown hanya muncul jika mahasiswa mencapai **Semester 5 ke atas**.
- **Deteksi Bentrok (Conflict Detection)**: Sistem secara otomatis menolak input jadwal kuliah jika terjadi tabrakan pada:
    1. Dosen (mengajar di 2 kelas di jam yang sama).
    2. Ruangan (digunakan 2 kelas di jam yang sama).
    3. Kelompok Kelas (1 kelas punya 2 matkul di jam yang sama).

## 5. Tampilan Jadwal (UI/UX)
Redesign layout jadwal kuliah agar lebih informatif dan rapi.
- **Academic Table Style**: Menggunakan format tabel fakultas teknik dengan fitur `rowspan` pada kolom **Hari**.
- **Responsive Design**: Tampilan tetap rapi dan mudah dibaca baik di desktop maupun perangkat mobile.

## Checklist Hasil Akhir
- [✅] Migrasi Database (Username, Password Plain, Konsentrasi Logic)
- [✅] Autentikasi Hybrid (NIM/NIDN/Email)
- [✅] Fitur Biodata Lengkap & Upload Foto
- [✅] Validasi Bentrok Jadwal Otomatis
- [✅] UI Jadwal Tabel Akademik (Rowspan)
- [✅] Logika Konsentrasi Dinamis Semester 5+

---
**Status: Final & Terverifikasi**
