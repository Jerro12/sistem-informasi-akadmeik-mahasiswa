# Rencana Implementasi & Pelacakan Revisi SIAKAD

Dokumen ini digunakan sebagai acuan utama untuk melacak progres pengerjaan revisi pada sistem SIAKAD. Kita akan menyelesaikan tugas-tugas ini secara bertahap (satu per satu) demi menjaga kualitas dan konsistensi kode.

---

## 📌 DAFTAR MODUL REVISI & CHECKLIST

### 1. 🏢 Akun Program Studi (Prodi)
Modul ini mencakup pembuatan dashboard dan hak akses khusus untuk Program Studi guna memantau skripsi, ujian, dan kerja praktek mahasiswa.
- [x] **1.1. Pengajuan Skripsi**
  * Mahasiswa mengajukan, dan Prodi dapat melihat serta memvalidasi/menyetujui pengajuan skripsi tersebut.
- [x] **1.2. Penentuan Penguji dan Pembimbing**
  * Fitur bagi prodi untuk memilih dan menetapkan Dosen Pembimbing serta Dosen Penguji untuk mahasiswa yang skripsinya disetujui.
- [x] **1.3. Pembuatan Jadwal Daftar Ujian**
  * Prodi dapat membuat jadwal pendaftaran ujian (ujian proposal, ujian hasil, ujian sidang komprehensif, dll).
- [x] **1.4. Validasi Persyaratan Ujian (Approve/Reject)**
  * Halaman khusus prodi untuk meninjau berkas persyaratan ujian yang diupload mahasiswa, lengkap dengan tombol **Setujui (Approve)** dan **Tolak (Reject)**.
- [x] **1.5. Monitoring Bimbingan**
  * Fitur untuk memantau log/kartu bimbingan mahasiswa dengan dosen pembimbing (melihat frekuensi bimbingan, catatan, dan progres).
- [x] **1.6. Manajemen Kerja Praktek (KP)**
  * Fitur pengelolaan pengajuan, pembimbing, dan monitoring untuk Kerja Praktek (KP) mahasiswa.

---

### 2. 🎓 Akun Mahasiswa
Modul untuk meningkatkan antarmuka dan alur akademik mahasiswa agar lebih interaktif dan sesuai kebutuhan administratif masing-masing prodi.
- [x] **2.1. Skripsi/TA - Upload Persyaratan Berkas Ujian**
  * Halaman upload berkas ujian (contoh: sertifikat TOEFL, bebas pustaka, dll) yang disesuaikan dengan program studi (Prodi) masing-masing.
- [x] **2.2. Jadwal Ujian & Syarat Ujian Berdasarkan Prodi**
  * Halaman pendaftaran jadwal ujian dan informasi persyaratan ujian yang disesuaikan secara dinamis berdasarkan Prodi mahasiswa.
- [x] **2.3. Halaman KRS (Program Mata Kuliah & Filter Semester)**
  * Mengubah tampilan dari jadwal kelas menjadi daftar program mata kuliah yang diambil.
  * Menampilkan mata kuliah secara terorganisir/filter berdasarkan Semester Ganjil atau Genap.
- [x] **2.4. Jadwal Kuliah Berdasarkan Kelas**
  * Halaman jadwal kuliah yang difilter dan ditampilkan berdasarkan kelompok kelas mahasiswa.
- [x] **2.5. Menyembunyikan Informasi Kelas**
  * Memastikan informasi kelas mahasiswa (misal: "Kelas A", "Kelas B") tidak ditampilkan langsung di antarmuka publik/mahasiswa jika tidak diperlukan.
- [x] **2.6. Penambahan Fitur Transkrip Nilai**
  * Pembuatan halaman transkrip nilai akademik lengkap (khusus menampilkan IPK, SKS lulus, dan seluruh mata kuliah yang telah diselesaikan beserta nilainya).
- [x] **2.7. Pembaruan Form Biodata (Alamat Lengkap & Tanggal Lahir)**
  * Menambahkan field dropdown/input untuk: **Provinsi**, **Kecamatan**, **Kelurahan**, dan **Desa**.
  * Merapikan tata letak (layout) agar kolom **Tempat Lahir** dan **Tanggal Lahir** berdekatan (satu baris / berdampingan).
- [x] **2.8. Pengajuan KRS - Upload Pembayaran**
  * Menghapus alur pembayaran manual/otomatis yang ada sebelumnya pada pengajuan KRS, digantikan dengan form **Upload Bukti Pembayaran**.
- [x] **2.9. Penanganan Khusus Mata Kuliah Skripsi**
  * Skripsi diprogram di KRS tetapi **tidak dimunculkan** di Jadwal Kelas (hanya muncul di pelacakan skripsi).
- [x] **2.10. Perbaikan Penghapusan Mahasiswa**
  * Menghapus validasi pembatasan sehingga data mahasiswa dapat dihapus dengan sukses beserta seluruh data KRS-nya via cascade delete.

---

### 3. 🔑 Akun Superadmin
Pembersihan menu dan peningkatan kontrol pembayaran mahasiswa.
- [x] **3.1. Monitoring Pembayaran & Validasi**
  * Menambahkan filter pencarian berdasarkan **Program Studi (Prodi)** dan **Fakultas** pada dashboard monitoring pembayaran.
  * Menambahkan tombol tindakan untuk **Approve/Verifikasi** bukti pembayaran KRS/Uang Kuliah yang diupload mahasiswa.
- [x] **3.2. Pembersihan Sidebar Akademik**
  * Menghapus menu-menu yang tidak diperlukan oleh Superadmin di sidebar (seperti Skripsi, KP, dll. karena wewenang dipindah ke Prodi/Fakultas).
- [x] **3.3. Penyederhanaan Tahun Akademik**
  * Menghapus kolom/periode perkuliahan yang tidak relevan pada manajemen Tahun Akademik.

---

### 4. 👨‍🏫 Akun Dosen
- [x] **4.1. Jadwal Mengajar Dosen**
  * Halaman khusus bagi dosen untuk melihat jadwal mengajar mereka sendiri di setiap semester (mata kuliah, hari, jam, ruang, dan kelas).
- [x] **4.2. Pembaruan Form Biodata Dosen**
  * Menambahkan field dropdown/input untuk: **Provinsi**, **Kecamatan**, **Kelurahan**, dan **Desa**.
  * Merapikan tata letak agar kolom **Tempat Lahir** dan **Tanggal Lahir** berdekatan/berdampingan.
- [x] **4.3. Perbaikan Penghapusan Dosen**
  * Menghapus validasi pembatasan sehingga data dosen dapat dihapus dengan sukses beserta data kelas bimbingan via cascade delete.

---

### 5. 🏛️ Akun Fakultas
- [x] **5.1. Input Nilai Mahasiswa**
  * Halaman bagi admin fakultas untuk menginputkan nilai mata kuliah mahasiswa per kelas.
- [x] **5.2. Pembersihan & Hak Akses Akademik**
  * Menambahkan fitur **Approve KRS** oleh pihak Fakultas.
  * Menghapus menu **Skripsi/TA** dan **KP** dari sidebar menu Fakultas (karena dikelola oleh Prodi).
- [x] **5.3. Pembaruan Antarmuka Mahasiswa (Nama Lengkap)**
  * Mengganti kolom **Email** menjadi **Nama Lengkap** pada tabel daftar mahasiswa di tingkat Fakultas.
- [x] **5.4. Kustomisasi Warna Beranda Berdasarkan Fakultas**
  * Tampilan beranda/dashboard menyesuaikan warna Fakultas yang sedang aktif: FT (merah), FEB (kuning), FH (coklat), FAI (hijau tua), FAPETRIK (hijau muda), Fikes (ungu), FKIP (oranye).

---

## 🛠️ Rencana Alur Kerja (Workflow)
Kita akan mengerjakan tugas-tugas ini dengan membaginya ke dalam sub-tugas kecil. Setelah setiap tugas selesai, kita akan memperbarui status di file ini (`[✅]`) sebelum melanjutkan ke tugas berikutnya.

Mari kita sepakati bersama alur kerja ini. Silakan beri tahu jika ada yang ingin disesuaikan!
