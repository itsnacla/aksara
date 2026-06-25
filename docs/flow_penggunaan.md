# Panduan Alur Penggunaan Aplikasi Aksara (Sistem Informasi Akademik)

Dokumen ini menjelaskan alur penggunaan (user flow) operasional dari Sistem Informasi Akademik "Aksara" mulai dari instalasi awal, setup data master, proses akademik harian, evaluasi berbasis AI, hingga pelaporan akhir semester dan pengarsipan jangka panjang.

---

## 1. Setup Awal & Master Data (Oleh Admin)

Alur pertama kali saat sistem dijalankan. Administrator bertugas untuk menyiapkan pondasi sistem secara menyeluruh.

1. **Login & Otentikasi Admin:**
   - Admin login melalui halaman `/admin/login`.
   - Menggunakan `ImpersonateController`, Admin pusat juga dapat menggunakan fungsi **Login As** (Impersonate) untuk mensimulasikan dan menguji tampilan dari sudut pandang Guru atau Siswa tertentu tanpa perlu mengetahui *password* mereka.
2. **Pengaturan Sekolah (School Settings) & Sinkronisasi Wilayah:**
   - Masuk ke menu **Pengaturan Sekolah**.
   - Isi kelengkapan data sekolah seperti Nama Sekolah, NPSN, Alamat Lengkap, Kepala Sekolah, NIP, dan logo.
   - Sistem akan memanggil `KemendikbudService`, `RegionService`, dan `SchoolRegionService` untuk menstandarisasi referensi kode pos dan data wilayah secara otomatis ke database pusat.
3. **Konfigurasi AI Brain & RAG Pipeline:**
   - Masuk ke **Chatbot Settings**.
   - Admin memasukkan API Key (Gemini/OpenAI) dan mengaktifkan fitur asisten.
   - Mengisi dokumen panduan operasional sekolah ke dalam **AksaraKnowledgeBase** agar sistem RAG (PG Vector) dapat dilatih dan `AksaraAssistant` bisa merespon pertanyaan wali murid secara kontekstual.
4. **Pengaturan Tahun Ajaran & Kalender Akademik:**
   - Masuk ke **Academic Years** (Tahun Ajaran) dan set Tahun Ajaran aktif (misal: 2023/2024 - Ganjil).
   - Atur **Day Configs** dan **Time Slots** untuk menentukan pemetaan hari efektif dan pembagian jam pelajaran (misal: Jam ke-1: 07.00 - 07.45).
5. **Manajemen Pengguna & Otorisasi:**
   - Admin membuat/mengimpor data akun Guru, Staff, Siswa, dan Orang Tua (`StudentParents`).
   - Hak akses dikontrol penuh menggunakan *Filament Shield* (Roles & Permissions).
6. **Data Master Kurikulum & P5 (Kurikulum Merdeka):**
   - Mendefinisikan **Levels** (Tingkat) dan **Classrooms** (Ruang Kelas).
   - Memasukkan **Subjects** (Mata Pelajaran), **Subject Report Group** (Kelompok Mapel A/B/C), dan **Learning Objectives** (Tujuan Pembelajaran).
   - Membangun struktur **Profil Pelajar Pancasila** (P5): Menyiapkan `P5Theme`, `P5Project`, `P5Group`, dan `GraduateProfileSubdimension` untuk instrumen penilaian karakter.
   - Menambahkan daftar **Extracurriculars** (Ekstrakurikuler) dan **Cocurricular** (Kokurikuler).
7. **Pendaftaran Rombel & Cetak Kartu Pelajar:**
   - Mendistribusikan Siswa ke dalam **Study Groups** (Rombongan Belajar).
   - Setelah selesai, Admin/TU dapat mencetak Kartu Pelajar otomatis melalui `StudentCardController` lengkap dengan QR Code mandiri siswa.

---

## 2. Persiapan KBM & Penjadwalan (Oleh Admin/Kurikulum)

Penjadwalan KBM merupakan tahap kritis untuk memastikan tidak ada jam kosong.

1. **Pembuatan Jadwal Terotomatisasi:**
   - Masuk ke menu **Schedules** (Jadwal Mengajar).
   - Fitur `ScheduleGeneratorService` akan membantu plotting jadwal secara dinamis berdasarkan beban mengajar guru (Mata Pelajaran, Kelas, Hari, Time Slot).
   - Validasi otomatis mencegah bentrok (`TeacherSchedules` conflict checking).
2. **Pengaturan Wali Kelas:**
   - Menugaskan Guru sebagai Wali Kelas di masing-masing Rombel untuk keperluan akses monitoring nilai dan validasi cetak Rapor.

---

## 3. Operasional Harian & Komunikasi Terpusat (Realtime)

Aktivitas reguler yang berjalan terus-menerus sepanjang semester.

### Absensi & Ekosistem WhatsApp Gateway (Fonnte)
1. **Pencatatan Kehadiran (Attendances):**
   - Guru / Admin mencatat absensi manual di kelas.
   - **QR Scan Mandiri**: Siswa dapat memindai kartu pelajar di gerbang sekolah melalui antarmuka `Livewire\QrScanStandalone`.
2. **Perizinan Terpadu (Student Leaves):**
   - Siswa / Orang Tua mengunggah surat dokter melalui menu `StudentLeaveController` di Portal. Admin/Guru me- *review* dan memberi *approval* (Approve/Reject).
3. **Notifikasi & Broadcast Otomatis:**
   - Saat absensi tersimpan, sistem merilis event `AttendanceLogged`.
   - *Background Jobs* (`SendWhatsAppAttendanceNotification`) dipanggil oleh `WAService` untuk mengirimkan chat konfirmasi *realtime* ke WhatsApp Orang Tua (menggunakan provider Fonnte).
   - Admin juga dapat menggunakan fitur *Broadcast* (`SendWhatsAppBroadcast`) untuk pengumuman sekolah atau tagihan.

### Portal Monitoring & Interaksi AI
1. **Realtime Dashboard Siswa/Ortu:**
   - Melalui `PortalController`, Siswa dan Orang Tua login ke portal *Front-end*.
   - Data jadwal harian, nilai terbaru, dan grafik kehadiran dipancarkan secara seketika (*Realtime*) menggunakan WebSocket (Laravel Reverb / Echo).
2. **AI Assistants Terpadu:**
   - **Untuk Siswa/Ortu**: Dapat *chatting* dengan `AksaraAssistant` terkait info jadwal, aturan sekolah, atau rekap absensi harian (terhubung dengan PG Vector RAG).
   - **Untuk Admin/Kepala Sekolah**: `DataScientistAssistant` dapat dipanggil untuk menganalisis statistik kelulusan, rata-rata kehadiran sekolah, atau performa guru secara tekstual (Natural Language SQL querying).

---

## 4. Evaluasi Akademik & Kurikulum Merdeka (Input oleh Guru)

Sistem evaluasi membagi tugas antara guru mapel dan wali kelas.

1. **Input Nilai Akademik (Grades):**
   - Guru masuk ke menu **Grades**. Di sana ada integrasi `GradeInputSettings` untuk menentukan skala dan bobot penilaian.
   - Nilai Sumatif/Formatif dan *Learning Objective* spesifik dimasukkan per siswa.
2. **Monitoring Progress Pengisian Nilai (Grade Monitoring):**
   - Agar tidak ada nilai yang bolong saat cetak rapor, fitur **Grade Monitoring** menggunakan `GradeProgressBuilder` memvisualisasikan persentase kelengkapan nilai dari seluruh guru secara *live*. Admin dapat langsung mengingatkan guru yang terlambat.
3. **Validasi & Kunci Nilai:**
   - Jika waktu pengisian habis, status `StatusPenilaian` akan diubah menjadi *Locked*, sehingga guru tidak bisa lagi mengubah nilai tanpa persetujuan Admin.
4. **Penilaian Karakter P5 & Ekstrakurikuler:**
   - Fasilitator P5 masuk ke proyek masing-masing untuk memberikan rubrik penilaian profil pelajar pancasila (`GraduateProfile`).
   - Guru pembina mengisi `ExtracurricularGrade`.
5. **Deskripsi Otomatis Wali Kelas:**
   - Wali kelas bertugas mengecek rekap akhir. Mereka menggunakan AI `WaliKelasAgent` untuk men-*generate* narasi catatan perkembangan siswa yang personal berdasarkan tren nilai dan absensinya secara otomatis.

---

## 5. Pelaporan, Rapor, & Arsip (Akhir Semester)

Fase penutupan semester dan manajemen histori data.

1. **Cetak Pelengkap Rapor (`PelengkapRapor`):**
   - Mencetak halaman depan rapor (Biodata Siswa, Identitas Sekolah) via `PrintController`.
2. **Generate E-Rapor (`RaporService`):**
   - Sistem merangkum seluruh tabel (*Academic Grades*, *P5*, *Extracurricular*, *Attendance*, dan *Wali Kelas Notes*) ke dalam file **PDF E-Rapor** formal yang siap dicetak dan ditandatangani.
3. **Pengarsipan Buku Induk Otomatis (`BukuIndukService`):**
   - Tidak perlu lagi menyalin nilai Rapor ke Buku Induk fisik secara manual.
   - `BukuIndukDataBuilder` akan mengumpulkan seluruh histori 6 semester nilai siswa ke dalam format buku besar (*Ledger*) dinamis di menu **Buku Induk**.
4. **Kenaikan Kelas (PromotionService) & Kelulusan:**
   - Admin menjalankan proses kenaikan kelas masal menggunakan `PromotionService`. Siswa otomatis berpindah tingkat.
   - Saat siswa tingkat akhir (misal Kelas 12) lulus, profil mereka diarsipkan ke dalam **Graduate Profile** untuk pencatatan *Tracer Study* alumni.

---

## Ringkasan Ekosistem & Alur Data

`Setup (Integrasi Kemendikbud & AI Brain)` ➔ `Generate Jadwal & QR Siswa` ➔ `KBM (Absensi Standalone + WA Fonnte Gateway)` ➔ `Evaluasi (Grade Progress Monitor + Status Lock)` ➔ `AI WaliKelasAgent (Komentar Rapor)` ➔ `Cetak E-Rapor PDF` ➔ `Sinkronisasi Buku Induk` ➔ `Promotion Kenaikan Kelas`

*Sistem Aksara bukan hanya sekadar database CRUD, melainkan ekosistem pintar (AI-driven) dengan komunikasi realtime (Reverb & WhatsApp) yang menihilkan kerja manual berulang bagi guru maupun tenaga administrasi sekolah.*
