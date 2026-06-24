# Panduan Alur Penggunaan Aplikasi Aksara (Sistem Informasi Akademik)

Dokumen ini menjelaskan alur penggunaan (user flow) dari Sistem Informasi Akademik "Aksara" mulai dari instalasi awal, setup data master, proses akademik harian, hingga pelaporan akhir semester.

---

## 1. Setup Awal & Master Data (Oleh Admin)

Alur pertama kali saat sistem dijalankan. Administrator bertugas untuk menyiapkan pondasi sistem.

1. **Login Admin:**
   - Admin login melalui halaman `/admin/login` menggunakan kredensial administrator.
2. **Pengaturan Sekolah (School Settings):**
   - Masuk ke menu **Pengaturan Sekolah**.
   - Isi kelengkapan data sekolah seperti Nama Sekolah, NPSN, Alamat Lengkap, Kepala Sekolah, NIP, Logo Sekolah, dan Logo Pemda.
   - Data ini akan digunakan sebagai kop surat dan identitas di seluruh cetakan (Rapor, Buku Induk, dll).
3. **Pengaturan Tahun Ajaran & Hari Efektif:**
   - Masuk ke **Academic Years** (Tahun Ajaran).
   - Buat Tahun Ajaran dan Semester aktif (misal: 2023/2024 - Ganjil). Set sebagai *Active*.
   - Atur **Day Configs** untuk menentukan hari efektif pembelajaran.
4. **Manajemen Pengguna (Akun):**
   - Admin membuat atau mengimpor data akun Guru, Siswa, dan Orang Tua.
   - Hak akses akan otomatis terbagi berdasarkan *Role* masing-masing (Filament Shield).
5. **Data Master Kurikulum:**
   - **Tingkat Kelas (Levels)** dan **Ruang Kelas (Classrooms/Rombel)** dibuat.
   - **Mata Pelajaran (Subjects)** dan **Tujuan Pembelajaran (Learning Objectives)** di-input oleh Admin atau Kurikulum.
   - **Ekstrakurikuler** didefinisikan.
6. **Pembagian Rombongan Belajar (Rombel):**
   - Admin/Operator memasukkan Siswa ke dalam Kelas/Rombel yang bersesuaian di tahun ajaran tersebut.

---

## 2. Persiapan KBM & Penjadwalan (Oleh Admin/Kurikulum)

Setelah data master siap, jadwal kelas harus diatur sebelum pembelajaran dimulai.

1. **Pembuatan Jadwal Mengajar:**
   - Masuk ke menu **Jadwal Mengajar (List Schedules)**.
   - Admin mengatur jadwal setiap guru: mengajar Mata Pelajaran apa, di Kelas mana, pada Hari dan Jam ke berapa.
   - Terdapat fitur auto-generate atau plotting manual untuk memastikan tidak ada jam kosong atau jadwal bentrok.
2. **Pengaturan Wali Kelas:**
   - Menetapkan guru mana yang menjadi Wali Kelas untuk masing-masing rombel. Wali kelas akan berwenang mencetak rapor untuk kelasnya.

---

## 3. Operasional Harian (Oleh Guru, Siswa, dan Admin)

Aktivitas yang terjadi setiap hari kerja selama tahun ajaran berlangsung.

### Absensi (Kehadiran)
1. **Guru / Admin:**
   - Mencatat absensi siswa (Hadir, Sakit, Izin, Alpa) melalui menu **Attendances**.
   - Opsi lain: Siswa melakukan pemindaian **QR Scan** mandiri jika fitur diaktifkan.
2. **Siswa:**
   - Dapat mengajukan izin tidak masuk sekolah dengan mengunggah surat dokter/keterangan melalui fitur **Student Leave** (Izin Siswa) di portal mereka. Admin/Guru akan menyetujui izin tersebut.

### Pembelajaran & Interaksi
1. **Portal Siswa / Orang Tua:**
   - Siswa dan Orang Tua dapat login ke portal mereka (Bukan di /admin, tapi di halaman depan aplikasi).
   - Di *Dashboard*, mereka dapat melihat Rekap Kehadiran, Jadwal Pelajaran Hari ini, dan Nilai secara *Realtime*.
2. **Fitur Chatbot AI:**
   - Terdapat Chatbot AI terintegrasi (berdasarkan pengaturan *Chatbot Settings* di admin) yang dapat melayani tanya jawab untuk Siswa dan Orang tua seputar info sekolah atau panduan mandiri.

---

## 4. Evaluasi Akademik (Input Nilai oleh Guru)

Memasuki masa Ujian Tengah Semester (UTS) atau Ujian Akhir Semester (UAS).

1. **Input Nilai Akademik:**
   - Guru mata pelajaran masuk ke menu **Grades** (Nilai).
   - Memilih Kelas dan Mata Pelajaran yang diampunya.
   - Memasukkan nilai harian, nilai sumatif, atau formatif beserta deskripsi capaian (*Learning Objective*) untuk setiap siswa.
2. **Input Nilai Ekstrakurikuler:**
   - Guru Pembina Ekstrakurikuler masuk ke menu **Extracurricular Grades**.
   - Memberikan nilai (Sangat Baik, Baik, Cukup) dan deskripsi kegiatan untuk siswa yang mengikuti ekskul tersebut.
3. **Catatan Wali Kelas:**
   - Wali kelas memasukkan catatan perkembangan siswa (Sikap, Kerajinan, dll) sebagai bahan untuk pencetakan rapor.

---

## 5. Pelaporan & Akhir Semester (Oleh Wali Kelas/Admin)

Di akhir semester, hasil belajar siswa dibagikan dalam bentuk rapor dan dicatat ke arsip sekolah (Buku Induk).

1. **Pengecekan Kelengkapan Data:**
   - Wali kelas memastikan semua nilai mata pelajaran, ekskul, dan rekap absensi sudah terisi penuh oleh guru-guru.
2. **Cetak Pelengkap Rapor:**
   - Digunakan (biasanya untuk siswa kelas awal / siswa baru) untuk mencetak Biodata Diri, Identitas Sekolah, dan Tanda Tangan Kepala Sekolah (berukuran A4).
   - Akses dari halaman detail siswa -> klik aksi **Cetak Pelengkap Rapor**.
3. **Cetak Rapor Siswa:**
   - Wali kelas melakukan generate / **Cetak Rapor**.
   - Sistem akan mengkompilasi: Data Nilai Akademik, Deskripsi Capaian Pembelajaran, Ekstrakurikuler, dan Rekapitulasi Absensi ke dalam format PDF/Kertas A4.
   - Rapor ditandatangani dan dibagikan.
4. **Cetak Buku Induk (Arsip Jangka Panjang):**
   - Admin atau Petugas Tata Usaha (TU) masuk ke menu **Buku Induk**.
   - Fitur ini merangkum *seluruh* histori nilai siswa sejak kelas awal hingga akhir dalam satu lembar/buku F4/A4 yang berkesinambungan.
   - Buku Induk otomatis mengelompokkan nilai siswa per Semester dan Tahun Ajaran secara dinamis tanpa perlu input manual dua kali.
5. **Kelulusan / Profil Lulusan:**
   - Saat siswa lulus, Admin mengelola data **Graduate Profile** sebagai pencatatan alumni.

---

## Ringkasan Alur (Summary Diagram)

`Setup Admin` ➔ `Pembuatan Jadwal & Pembagian Kelas` ➔ `KBM Berjalan (Absensi & Izin)` ➔ `Ujian & Input Nilai (Guru)` ➔ `Cetak Rapor (Wali Kelas)` ➔ `Arsip Buku Induk (TU/Admin)`

*Orang Tua & Siswa secara paralel dapat terus memantau progress kehadiran dan akademik melalui Portal mereka masing-masing sepanjang alur di atas berjalan.*
