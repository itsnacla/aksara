# Aksara Development Roadmap & Division

Dokumen ini menjelaskan pembagian tugas (Job Division) pengembangan proyek **Aksara** secara komprehensif, arsitektur teknis, dan alur kerja (workflow) untuk memastikan setiap tim pengembang dapat bekerja secara terstruktur, paralel, dan meminimalisir bentrokan (*blocking/conflict*). 

Pembagian ini dipetakan secara **Konseptual (Domain-Driven)** berdasarkan seluruh modul/fitur (Filament Resources, Services, dan Controllers) yang ada di dalam *codebase*.

---

## 🛠️ 1. Tech Stack & Architecture

Berdasarkan struktur *codebase* saat ini, spesifikasi teknologi yang digunakan adalah:

*   **Core Framework**: Laravel 13 & PHP 8.3+
*   **Database**: PostgreSQL 17 dengan ekstensi **PG Vector** (mendukung kapabilitas RAG dan AI Knowledge Base).
*   **Admin Panel / Backoffice**: **Filament ~5.0** (Resource-based UI & TALL Stack).
*   **Frontend & Asset Bundler**: Vite & Tailwind CSS 4.
*   **Realtime Communication**: Laravel Reverb (^1.0) & Echo untuk *realtime updates*.
*   **Role-Based Access Control (RBAC)**: Filament Shield (Spatie Permission).
*   **AI Engine**: *Native* terintegrasi melalui *package* `laravel/ai` (menghapus dependensi pada *microservice* FastAPI terpisah), memanfaatkan model Gemini/OpenAI untuk sistem *Agent* (`WaliKelasAgent`) dan RAG (`AksaraKnowledgeBase`).

---

## 👥 2. Conceptual Job Division (Pembagian Tugas Konseptual)

Untuk mencegah *overlap* pekerjaan, seluruh modul diisolasi berdasarkan domain bisnis sekolah. Berikut adalah pemetaan presisi untuk setiap *developer*:

### 🧠 A. Najla (System Core, Integrations & Intelligent Workspace)
Fokus pada fondasi sistem, integrasi eksternal (Kemendikbud/Wilayah), manajemen hak akses, dan ekosistem Kecerdasan Buatan (AI).

*   **Core Admin & Security (Manajemen Hak Akses)**:
    *   **Filament Resources**: `Users`, `SchoolSettings`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/UserResource/`
        *   `app/Filament/Resources/SchoolSettingResource/`
        *   `app/Models/User.php`
        *   `app/Models/SchoolSetting.php`
        *   `app/Http/Controllers/Portal/ImpersonateController.php`
    *   **Fitur**: Pengelolaan hak akses dinamis dengan Filament Shield (Roles & Permissions), serta pengaturan identitas sekolah.
*   **System Integrations (Sinkronisasi Eksternal)**:
    *   **Services**: `RegionService`, `SchoolRegionService`, `KemendikbudService`.
    *   **Direktori/File Utama**:
        *   `app/Services/RegionService.php`
        *   `app/Services/SchoolRegionService.php`
        *   `app/Services/KemendikbudService.php`
    *   **Fitur**: Menangani sinkronisasi data wilayah geografis dan standarisasi data referensi pendidikan.
*   **Intelligent Workspace (AI Brain & Chatbot)**:
    *   **Filament Resources**: `ChatbotSettings`.
    *   **Controllers/Services/Agents**: `ChatbotController`, `WaliKelasAgent`, `AksaraAssistant`, `DataScientistAssistant`, `AksaraKnowledgeBase`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/ChatbotSettingResource/`
        *   `app/Http/Controllers/ChatbotController.php`
        *   `app/Ai/Agents/WaliKelasAgent.php`
        *   `app/Ai/Agents/AksaraAssistant.php`
        *   `app/Ai/Agents/DataScientistAssistant.php`
        *   `app/Ai/AksaraKnowledgeBase.php`
        *   `app/Models/ChatbotSetting.php`
    *   **Fitur**: Mengelola RAG Pipeline dengan PG Vector, *chatbot* pintar di *dashboard*, analisis *Data Science* melalui Assistant, dan mengatur *provider* model AI (Gemini/OpenAI).

### 🏫 B. Nada (Academic Registry, KBM Operations & Attendance)
Fokus pada tata kelola entitas fisik (manusia & ruangan), data referensi kurikulum, dan operasional harian Kegiatan Belajar Mengajar (KBM).

*   **Academic Master Data (Data Referensi Kurikulum)**:
    *   **Filament Resources**: `AcademicYears`, `Levels`, `Subjects`, `SubjectReportGroup`, `SubjectReportMapping`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/AcademicYearResource/`
        *   `app/Filament/Resources/LevelResource/`
        *   `app/Filament/Resources/SubjectResource/`
        *   `app/Filament/Resources/SubjectReportGroupResource/`
        *   `app/Filament/Resources/SubjectReportMappingResource/`
        *   `app/Models/AcademicYear.php`, `app/Models/Level.php`, `app/Models/Subject.php`, `app/Models/SubjectReportGroup.php`, `app/Models/SubjectReportMapping.php`
    *   **Fitur**: Struktur dasar tahun ajaran, tingkat kelas, dan pemetaan mata pelajaran umum vs muatan lokal.
*   **Registry & SDM (Manajemen Entitas Manusia)**:
    *   **Filament Resources**: `Teachers`, `Staff`, `Students`, `StudentParents`, `BukuInduk`.
    *   **Controllers/Services**: `BukuIndukService`, `BukuIndukDataBuilder`, `PromotionService`, `StudentCardController`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/TeacherResource/`
        *   `app/Filament/Resources/StaffResource/`
        *   `app/Filament/Resources/StudentResource/`
        *   `app/Filament/Resources/StudentParentResource/`
        *   `app/Filament/Resources/BukuIndukResource/`
        *   `app/Services/Academic/BukuIndukService.php`
        *   `app/Services/Academic/BukuIndukDataBuilder.php`
        *   `app/Services/Academic/PromotionService.php`
        *   `app/Http/Controllers/StudentCardController.php`
        *   `app/Models/Teacher.php`, `app/Models/Staff.php`, `app/Models/Student.php`, `app/Models/StudentParent.php`
    *   **Fitur**: Pendataan riwayat hidup siswa (Buku Induk), proses kenaikan kelas, dan fitur cetak Kartu Pelajar.
*   **KBM Operations (Penjadwalan & Rombel)**:
    *   **Filament Resources**: `Classrooms`, `StudyGroups` (Rombel), `DayConfigs`, `TimeSlots`, `Schedules`, `TeacherSchedules`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/ClassroomResource/`
        *   `app/Filament/Resources/StudyGroupResource/`
        *   `app/Filament/Resources/DayConfigResource/`
        *   `app/Filament/Resources/TimeSlotResource/`
        *   `app/Filament/Resources/ScheduleResource/`
        *   `app/Filament/Resources/TeacherScheduleResource/`
        *   `app/Services/Academic/ScheduleGeneratorService.php`
        *   `app/Models/Classroom.php`, `app/Models/StudyGroup.php`, `app/Models/DayConfig.php`, `app/Models/TimeSlot.php`, `app/Models/Schedule.php`, `app/Models/TeacherSchedule.php`
    *   **Fitur**: Algoritma distribusi jadwal mengajar guru (`ScheduleGeneratorService`) dan pemetaan siswa ke dalam rombongan belajar.
*   **Attendance & Leaves (Kehadiran & Perizinan)**:
    *   **Filament Resources**: `Attendances`, `StudentLeaves`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/AttendanceResource/`
        *   `app/Filament/Resources/StudentLeaveResource/`
        *   `app/Livewire/QrScanStandalone.php`
        *   `app/Http/Controllers/Portal/StudentLeaveController.php`
        *   `app/Events/AttendanceLogged.php`
        *   `app/Models/Attendance.php`, `app/Models/StudentLeave.php`
    *   **Fitur**: Sistem presensi mandiri (QR-based Scanner) melalui `Livewire\QrScanStandalone` dan manajemen izin/sakit siswa, yang juga me-*trigger* *Event* notifikasi kehadiran.

### 📊 C. Septian (Evaluation, P5, Portals & Communication Hub)
Fokus pada asesmen/evaluasi siswa (termasuk Kurikulum Merdeka P5), pelaporan hasil akhir (Raport), serta portal komunikasi dengan Siswa/Orang Tua.

*   **Asesmen & Evaluasi (Penilaian Formatif/Sumatif)**:
    *   **Filament Resources**: `GradeInputSettings`, `LearningObjective` (Tujuan Pembelajaran), `Grades`, `StatusPenilaian`, `GradeMonitoring`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/GradeInputSettingResource/`
        *   `app/Filament/Resources/LearningObjectiveResource/`
        *   `app/Filament/Resources/GradeResource/`
        *   `app/Filament/Resources/StatusPenilaianResource/`
        *   `app/Filament/Resources/GradeMonitoringResource/`
        *   `app/Services/Academic/GradeProgressBuilder.php`
        *   `app/Models/LearningObjective.php`, `app/Models/Grade.php`, `app/Models/StudentGrade.php`
    *   **Services/Fitur**: Modul *GradeMonitoring* agar admin dapat melacak progres guru yang belum mengisi nilai, pembangun data progres (`GradeProgressBuilder`), serta input nilai harian/ujian.
*   **Kurikulum Merdeka P5 & Ekstrakurikuler**:
    *   **Filament Resources**: `P5Theme`, `P5Project`, `GraduateProfile` (Profil Pelajar Pancasila), `Extracurriculars`, `ExtracurricularGrade`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/P5ThemeResource/`
        *   `app/Filament/Resources/P5ProjectResource/`
        *   `app/Filament/Resources/GraduateProfileResource/`
        *   `app/Filament/Resources/ExtracurricularResource/`
        *   `app/Filament/Resources/ExtracurricularGradeResource/`
        *   `app/Models/P5Theme.php`, `app/Models/P5Project.php`, `app/Models/P5Group.php`, `app/Models/GraduateProfile.php`, `app/Models/GraduateProfileSubdimension.php`, `app/Models/Extracurricular.php`, `app/Models/ExtracurricularGrade.php`, `app/Models/Cocurricular.php`
    *   **Fitur**: Instrumen penilaian karakter siswa berbasis proyek (P5) dan kegiatan ekstrakurikuler.
*   **Reporting (E-Raport)**:
    *   **Filament Resources**: `PelengkapRapor`, `Rapor`.
    *   **Controllers/Services**: `PrintController`, `RaporService`.
    *   **Direktori/File Utama**:
        *   `app/Filament/Resources/PelengkapRaporResource/`
        *   `app/Filament/Resources/RaporResource/`
        *   `app/Http/Controllers/PrintController.php`
        *   `app/Services/Academic/RaporService.php`
        *   `app/Models/StudentRapor.php`, `app/Models/EReport.php`
    *   **Fitur**: Kalkulasi nilai akhir secara otomatis, integrasi deskripsi/catatan *AI-generated*, dan cetak PDF Rapor Siswa.
*   **User Portals & Communication Hub**:
    *   **Controllers**: `PortalController` (Dashboard Portal Siswa & Orang Tua di luar Filament), `ReportController`.
    *   **Filament Resources**: `WhatsAppLogs`.
    *   **Services & Jobs**: `WAService`, `SendWhatsAppAttendanceNotification`, `SendWhatsAppBroadcast`, `SendWhatsAppNotification`.
    *   **Direktori/File Utama**:
        *   `app/Http/Controllers/Portal/PortalController.php`
        *   `app/Http/Controllers/ReportController.php`
        *   `app/Filament/Resources/WhatsAppLogResource/`
        *   `app/Services/WAService.php`
        *   `app/Jobs/SendWhatsAppAttendanceNotification.php`
        *   `app/Jobs/SendWhatsAppBroadcast.php`
        *   `app/Jobs/SendWhatsAppNotification.php`
        *   `app/Models/WhatsAppLog.php`, `app/Models/Notification.php`
    *   **Fitur**: *WhatsApp Gateway Hub* terpusat (Fonnte) untuk mengirim notifikasi absensi (*realtime* melalui *Jobs*), pesan *broadcast* (tagihan, nilai), serta portal monitoring *realtime* untuk Siswa & Orang Tua.

---

## 🔄 3. Parallel Workflow (Alur Kerja Paralel)

Kita mengadopsi pendekatan **"Contract-First Development"** agar tim dapat bekerja paralel secara asinkron.

### Strategi Eksekusi & Anti-Blocking:

1.  **Shared Database Schema (Model Layer)**:
    *   Seluruh *Migration* dan *Model* dasar wajib difinalisasi dan di-*merge* ke `development` di fase awal (Sprint 0).
    *   **Contoh Skenario**: Selama struktur tabel `students` dan `study_groups` sudah disepakati, **Septian** bisa langsung membangun logika algoritma `RaporService` dan `GradeMonitoring` tanpa harus menunggu **Nada** menyelesaikan antarmuka/UI Filament untuk `StudyGroups`.
2.  **Resource Isolation**:
    *   Arsitektur Filament mengisolasi 1 Modul = 1 Direktori (misal: `app/Filament/Resources/AttendanceResource`). Struktur ini secara alami menghindari *Merge Conflict* pada Git karena file yang disentuh saling berbeda (Orthogonal).
3.  **Mandatory Seeding**:
    *   Setiap developer **wajib** bergantung pada `DatabaseSeeder` dan *Factories* untuk pengujian.
    *   Telah disediakan seeder masif seperti `GradesAndReportsSeeder.php` untuk men-generate ribuan data dummy relasional (nilai, absensi, P5). Ini memungkinkan **Najla** melatih dan menguji prompt *WaliKelasAgent* dan PG Vector tanpa harus menunggu data riil di-*input* manual.

---

## 🤖 4. Technical Architecture Decisions

> [!TIP]
> **Transisi ke Native Laravel AI**:
> Tim memutuskan membuang arsitektur *Microservice FastAPI* (Python) yang lama. Eksekusi AI kini ditarik secara penuh ke dalam ekosistem PHP menggunakan `laravel/ai`.
> *   **Justifikasi**: Ekosistem tunggal (Monolith) ini menghilangkan kompleksitas *networking*, mempermudah otentikasi (Auth), dan memungkinkan AI mengakses relasi Eloquent ORM secara langsung dan efisien. Dukungan **PG Vector** pada PostgreSQL 17 membuat RAG (Retrieval-Augmented Generation) bisa berjalan *Self-Hosted*.

> [!NOTE]
> **WhatsApp Gateway Integration (Sementara)**:
> Untuk sementara waktu, gateway komunikasi resmi menggunakan layanan pihak ketiga **Fonnte**. Integrasi dilakukan menggunakan API Fonnte untuk mengirim notifikasi/pesan *broadcast* ke pengguna (tagihan, nilai, dll) tanpa memerlukan setup *Meta Business Platform* mandiri pada tahap awal.

---

## 🛠️ 5. Deployment & Verification Plan

### Automated Checks
*   **Testing Suite**: Jalankan `php artisan test` pada fitur-fitur kritikal seperti kalkulasi nilai rata-rata, *rate-limiter* absensi, dan konektivitas API pihak ketiga.
*   **Linting**: Menjalankan Laravel Pint (`./vendor/bin/pint`) terintegrasi pada Git Hook untuk menjaga konsistensi format kode tim (*PSR-12/Laravel Style*).

### Manual QA
*   **Role-Based Validation**: Menggunakan fungsi *Impersonate* (Login As) untuk menguji limitasi visual. Memastikan Guru SD Kelas 1 tidak bisa mengedit nilai Siswa Kelas 2, dan memastikan Siswa X tidak bisa melihat absen Siswa Y di Portal Mandiri.
