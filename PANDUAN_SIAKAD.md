# 📘 Panduan Operasional Sistem SIAKAD

Panduan ini menjelaskan alur kerja utama dalam sistem informasi akademik, mulai dari pengaturan data master oleh Admin hingga proses KRS oleh Mahasiswa.

---

## 🏗️ 1. Persiapan Awal (Wajib dilakukan setiap Semester Baru)

### A. Tahun Akademik
Setiap awal semester, Superadmin/Admin harus memastikan:
1.  Buka menu **Tahun Akademik**.
2.  Buat tahun baru (Contoh: 2024/2025 Ganjil).
3.  Set status menjadi **AKTIF**. 
    *Catatan: Hanya satu tahun akademik yang boleh aktif dalam satu waktu.*

### B. Verifikasi Pembayaran (Finance/Admin)
Mahasiswa hanya bisa KRS jika sudah membayar.
1.  Admin memasukkan data pembayaran mahasiswa di menu **Pembayaran**.
2.  Pastikan jenis pembayaran adalah **"KRS Semester"** dan statusnya **"Lunas"** untuk tahun akademik yang sedang aktif.

---

## 🏗️ 2. Manajemen Data Akademik (Admin Fakultas)

### A. Kurikulum & Konsentrasi
*   **Kurikulum**: Digunakan untuk membedakan daftar matkul antar angkatan (Contoh: Kurikulum 2020 vs Kurikulum Merdeka).
*   **Konsentrasi**: Digunakan jika Prodi memiliki penjurusan (Contoh: Teknik Informatika memiliki konsentrasi "Data Science" dan "Cyber Security").

### B. Mata Kuliah (Matkul)
*   **Wajib vs Pilihan**: Sistem membatasi mahasiswa hanya boleh mengambil **maksimal 3 matkul Pilihan** per semester.
*   **Filter Semester**: Isi kolom semester (1-8) dengan benar. Ini akan menjadi filter utama di halaman mahasiswa.

### C. Manajemen Kelas (Penjadwalan)
**PENTING**: Mahasiswa tidak mengambil Matkul, tapi mengambil **KELAS**.
1.  Setelah Matkul dibuat, Admin harus membuat **Kelas** untuk Matkul tersebut di semester aktif.
2.  Tentukan Dosen Pengampu dan Kapasitas Kelas.
3.  Jika Kelas belum dibuat, daftar pilihan di mahasiswa akan **KOSONG**.

---

## 🎓 3. Alur KRS Mahasiswa (Self-Service)

### Langkah 1: Pemilihan Mata Kuliah (Filter Otomatis)
Sistem melakukan filter cerdas:
1.  **Filter Semester**: Menampilkan matkul yang sesuai dengan semester aktif mahasiswa.
2.  **Hutang Matkul**: Matkul semester bawah yang belum pernah diambil akan otomatis muncul kembali.
3.  **Filter Prodi/Kurikulum**: Hanya menampilkan matkul yang relevan dengan prodi dan kurikulum mahasiswa.

### Langkah 2: Finalisasi & Kunci KRS (Patenkan)
Mahasiswa wajib menekan tombol **"Finalkan & Kunci KRS"** untuk mengakhiri pemilihan.
*   **Sebelum Dikunci**: Mahasiswa bisa bebas menambah/menghapus matkul.
*   **Setelah Dikunci**: Fitur edit hilang, data tersimpan permanen di database, dan tombol **"Cetak KRS"** muncul.

### Langkah 3: Cetak KRS
Mahasiswa mencetak dokumen fisik untuk dimintakan tanda tangan Kaprodi/Dosen PA.

---

## 🛠️ 4. Troubleshooting (Tanya Jawab)

| Masalah | Penyebab & Solusi |
|---------|-------------------|
| **Matkul tidak muncul di KRS mahasiswa?** | 1. Cek apakah **Kelas** untuk matkul tersebut sudah dibuat di semester aktif? <br> 2. Cek apakah semester matkul cocok dengan semester mahasiswa? <br> 3. Cek apakah prodi/kurikulum matkul cocok? |
| **Tombol KRS tidak bisa diklik?** | Biasanya karena mahasiswa tersebut belum tercatat **LUNAS** di semester aktif. |
| **Mahasiswa salah pilih dan sudah terlanjur kunci?** | Admin dapat mereset status KRS mahasiswa tersebut menjadi `draft` melalui database atau menu manajemen KRS Admin. |

---

## 🔐 5. Manajemen Akun Mahasiswa

Untuk menjaga validitas data NIM dan Prodi, sistem ini menggunakan alur **Pendaftaran Terpusat**:

1.  **Tidak Ada Pendaftaran Mandiri**: Mahasiswa tidak bisa mendaftar akun sendiri melalui halaman depan (fitur ini sengaja dinonaktifkan demi keamanan).
2.  **Pembuatan Akun oleh Admin**:
    *   Buka menu **Data Mahasiswa** > Klik **Tambah**.
    *   Admin memasukkan Nama, NIM, Email, Prodi, Angkatan, dan **Semester Sekarang**.
    *   Email dan NIM akan menjadi identitas login mahasiswa.
3.  **Akses Mahasiswa**: Setelah akun dibuat oleh Admin, mahasiswa baru bisa login menggunakan email/NIM tersebut untuk memulai proses pembayaran dan KRS.

---

