<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $tahun_ajaran
 * @property string $semester
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EReport> $eReports
 * @property-read int|null $e_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear whereSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear whereTahunAjaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AcademicYear whereUpdatedAt($value)
 */
	class AcademicYear extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $schedule_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Schedule $schedule
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $level_id
 * @property string $nama_kelas
 * @property int $walikelas_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Level $level
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @property-read \App\Models\Teacher $waliKelas
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereNamaKelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereWalikelasId($value)
 */
	class Classroom extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $academic_year_id
 * @property string $semester
 * @property string $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport whereSemester($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EReport whereUpdatedAt($value)
 */
	class EReport extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property string $nama_ekskul
 * @property string $nilai_kualitatif
 * @property string $deskripsi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereNamaEkskul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereNilaiKualitatif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereUpdatedAt($value)
 */
	class Extracurricular extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property int $academic_year_id
 * @property int $nilai_tugas
 * @property int $nilai_uts
 * @property int $nilai_uas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereNilaiTugas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereNilaiUas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereNilaiUts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereUpdatedAt($value)
 */
	class Grade extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $permission
 * @property string $keterangan
 * @property string $file_path
 * @property bool $is_approved
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveRequest whereUserId($value)
 */
	class LeaveRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_tingkatan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
 * @property-read int|null $classrooms_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereNamaTingkatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereUpdatedAt($value)
 */
	class Level extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $attendance_id
 * @property string $notification_type
 * @property string $title
 * @property string $message
 * @property bool $is_sent
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Attendance $attendance
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereAttendanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereNotificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $classroom_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property int $academic_year_id
 * @property string $hari
 * @property string $jam_mulai
 * @property string $jam_selesai
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\Classroom $classroom
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereClassroomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereJamMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereJamSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereUpdatedAt($value)
 */
	class Schedule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $nama_staff
 * @property string|null $jabatan
 * @property string|null $no_whatsapp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereNamaStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereNoWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereUserId($value)
 */
	class Staff extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $classroom_id
 * @property int $parent_id
 * @property string $nisn
 * @property string $nama_siswa
 * @property string|null $qr_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\Classroom $classroom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EReport> $eReports
 * @property-read int|null $e_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Extracurricular> $extracurriculars
 * @property-read int|null $extracurriculars_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\StudentParent $parent
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereClassroomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNamaSiswa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNisn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereQrCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUserId($value)
 */
	class Student extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $nama_wali
 * @property string|null $no_whatsapp
 * @property string $hubungan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereHubungan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereNamaWali($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereNoWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereUserId($value)
 */
	class StudentParent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_mapel
 * @property int $kkm
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereKkm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereNamaMapel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereUpdatedAt($value)
 */
	class Subject extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $nip
 * @property string $nama_guru
 * @property string|null $spesialisasi
 * @property bool $is_walikelas
 * @property bool $is_kepalasekolah
 * @property string|null $no_whatsapp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
 * @property-read int|null $classrooms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereIsKepalasekolah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereIsWalikelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereNamaGuru($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereNoWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereSpesialisasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUserId($value)
 */
	class Teacher extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LeaveRequest> $leaveRequests
 * @property-read int|null $leave_requests_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\StudentParent|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Staff|null $staff
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

