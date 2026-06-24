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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
 * @property-read int|null $classrooms_count
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
 * @property int $study_group_id
 * @property int|null $schedule_id
 * @property string $status
 * @property string|null $check_in
 * @property string|null $check_out
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string|null $catatan
 * @property \Illuminate\Support\Carbon|null $wa_sent_at
 * @property string|null $check_in_start
 * @property string|null $check_in_end
 * @property string|null $check_out_start
 * @property string|null $check_out_end
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Schedule|null $schedule
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\StudyGroup|null $studyGroup
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckInEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckInStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckOutEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckOutStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStudyGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereWaSentAt($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $primary_provider
 * @property string|null $fallback_providers
 * @property array|null $settings
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $openai_base_url
 * @property-read string $provider
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting whereFallbackProviders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting wherePrimaryProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatbotSetting whereUpdatedAt($value)
 */
	class ChatbotSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_ruangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudyGroup> $studyGroups
 * @property-read int|null $study_groups_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereNamaRuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classroom whereUpdatedAt($value)
 */
	class Classroom extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tema
 * @property string $nama_projek
 * @property string $fase
 * @property string|null $deskripsi
 * @property string|null $tahun_ajaran
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereFase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereNamaProjek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereTahunAjaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereTema($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cocurricular whereUpdatedAt($value)
 */
	class Cocurricular extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $academic_year_id
 * @property string $day
 * @property bool $is_closed
 * @property array $level_ids
 * @property int|null $max_time_slot_id
 * @property int|null $mandatory_subject_id
 * @property int|null $mandatory_time_slot_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Subject|null $mandatorySubject
 * @property-read \App\Models\TimeSlot|null $mandatoryTimeSlot
 * @property-read \App\Models\TimeSlot|null $maxTimeSlot
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereIsClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereLevelIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereMandatorySubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereMandatoryTimeSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereMaxTimeSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DayConfig whereUpdatedAt($value)
 */
	class DayConfig extends \Eloquent {}
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
 * @property string $nama_ekskul
 * @property string|null $kategori
 * @property int|null $nilai_minimum
 * @property int|null $coordinator_user_id
 * @property string|null $deskripsi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $coordinator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExtracurricularGrade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereCoordinatorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereKategori($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereNamaEkskul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereNilaiMinimum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Extracurricular whereUpdatedAt($value)
 */
	class Extracurricular extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $extracurricular_id
 * @property int $student_id
 * @property int $academic_year_id
 * @property string $predikat
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Extracurricular $extracurricular
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade whereExtracurricularId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade wherePredikat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExtracurricularGrade whereUpdatedAt($value)
 */
	class ExtracurricularGrade extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property int $academic_year_id
 * @property int $study_group_id
 * @property int $nilai_tugas
 * @property int $nilai_uts
 * @property int $nilai_uas
 * @property array<array-key, mixed>|null $optimal_tp_ids
 * @property array<array-key, mixed>|null $improved_tp_ids
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\StudyGroup|null $studyGroup
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereImprovedTpIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereNilaiTugas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereNilaiUas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereNilaiUts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereOptimalTpIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereStudyGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Grade whereUpdatedAt($value)
 */
	class Grade extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $academic_year_id
 * @property string $dimensi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GraduateProfileSubdimension> $subdimensions
 * @property-read int|null $subdimensions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile whereDimensi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfile whereUpdatedAt($value)
 */
	class GraduateProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $graduate_profile_id
 * @property string $subdimensi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GraduateProfile $graduateProfile
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension whereGraduateProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension whereSubdimensi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GraduateProfileSubdimension whereUpdatedAt($value)
 */
	class GraduateProfileSubdimension extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $subject_id
 * @property int $level_id
 * @property string|null $code
 * @property string $description
 * @property bool $is_active
 * @property int|null $academic_year_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudentGrade> $grades
 * @property-read int|null $grades_count
 * @property-read \App\Models\Level $level
 * @property-read \App\Models\Subject $subject
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningObjective whereUpdatedAt($value)
 */
	class LearningObjective extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_tingkatan
 * @property string|null $fase
 * @property bool $is_last_level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
 * @property-read int|null $classrooms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subject> $subjects
 * @property-read int|null $subjects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimeSlot> $timeSlots
 * @property-read int|null $time_slots_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereFase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereIsLastLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereNamaTingkatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Level whereUpdatedAt($value)
 */
	class Level extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int|null $attendance_id
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
 * @property int $p5_project_id
 * @property int $level_id
 * @property int $teacher_id
 * @property string $name
 * @property int|null $academic_year_id
 * @property int|null $study_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Level $level
 * @property-read \App\Models\P5Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @property-read \App\Models\StudyGroup|null $studyGroup
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereP5ProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereStudyGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Group whereUpdatedAt($value)
 */
	class P5Group extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $p5_theme_id
 * @property int $academic_year_id
 * @property string $fase
 * @property string $name
 * @property string|null $target_description
 * @property array|null $graduate_profile
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\P5Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Level> $levels
 * @property-read int|null $levels_count
 * @property-read \App\Models\P5Theme $theme
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereFase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereGraduateProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereP5ThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereTargetDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Project whereUpdatedAt($value)
 */
	class P5Project extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property int|null $academic_year_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\P5Project> $projects
 * @property-read int|null $projects_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|P5Theme whereUpdatedAt($value)
 */
	class P5Theme extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $study_group_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property string $hari
 * @property int $start_time_slot_id
 * @property int $end_time_slot_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear|null $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\TimeSlot $endTimeSlot
 * @property-read \App\Models\TimeSlot $startTimeSlot
 * @property-read \App\Models\StudyGroup $studyGroup
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereEndTimeSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereStartTimeSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereStudyGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereUpdatedAt($value)
 */
	class Schedule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $npsn
 * @property string|null $logo
 * @property string|null $logo_pemda
 * @property string|null $address
 * @property string|null $village
 * @property string|null $district
 * @property string|null $city
 * @property string|null $province
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property string|null $motto
 * @property bool $is_wa_enabled
 * @property string|null $wa_gateway_url
 * @property string|null $wa_gateway_token
 * @property string|null $wa_gateway_provider
 * @property string|null $wa_gateway_phone_param
 * @property string|null $wa_gateway_message_param
 * @property bool $wa_notify_attendance
 * @property bool $wa_notify_announcement
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereIsWaEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereLogoPemda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereMotto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereNpsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereVillage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWaGatewayMessageParam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWaGatewayPhoneParam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWaGatewayProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWaGatewayToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWaGatewayUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWaNotifyAnnouncement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWaNotifyAttendance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolSetting whereWebsite($value)
 */
	class SchoolSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $jabatan
 * @property string $status
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereNoWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Staff whereUserId($value)
 */
	class Staff extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string|null $nisn
 * @property string|null $nis
 * @property string $status
 * @property string|null $pob
 * @property \Illuminate\Support\Carbon|null $dob
 * @property string|null $gender
 * @property string|null $religion
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $village
 * @property string|null $district
 * @property string|null $city
 * @property string|null $province
 * @property bool $lives_with_parent
 * @property string|null $previous_school
 * @property string|null $nik
 * @property string|null $no_kk
 * @property string|null $no_akta_lahir
 * @property int|null $anak_ke
 * @property int|null $jumlah_saudara
 * @property float|null $tinggi_badan
 * @property float|null $berat_badan
 * @property string|null $golongan_darah
 * @property string|null $ayah_nik
 * @property string|null $ayah_nama
 * @property string|null $ayah_pendidikan
 * @property string|null $ayah_pekerjaan
 * @property string|null $ayah_penghasilan
 * @property string|null $ibu_nik
 * @property string|null $ibu_nama
 * @property string|null $ibu_pendidikan
 * @property string|null $ibu_pekerjaan
 * @property string|null $ibu_penghasilan
 * @property string|null $wali_nama
 * @property string|null $wali_pekerjaan
 * @property string|null $wali_hubungan
 * @property bool $is_buku_induk_generated
 * @property int|null $study_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EReport> $eReports
 * @property-read int|null $e_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExtracurricularGrade> $extracurricularGrades
 * @property-read int|null $extracurricular_grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Extracurricular> $extracurriculars
 * @property-read int|null $extracurriculars_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\P5Group> $p5Groups
 * @property-read int|null $p5_groups_count
 * @property-read \App\Models\StudentParent $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudentRapor> $studentRapors
 * @property-read int|null $student_rapors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudyGroup> $studyGroups
 * @property-read int|null $study_groups_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAnakKe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAyahNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAyahNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAyahPekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAyahPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereAyahPenghasilan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereBeratBadan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereGolonganDarah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIbuNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIbuNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIbuPekerjaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIbuPendidikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIbuPenghasilan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIsBukuIndukGenerated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereJumlahSaudara($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereLivesWithParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNisn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNoAktaLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereNoKk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student wherePob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student wherePreviousSchool($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStudyGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereTinggiBadan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereVillage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereWaliHubungan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereWaliNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereWaliPekerjaan($value)
 */
	class Student extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $learning_objective_id
 * @property int $academic_year_id
 * @property int $teacher_id
 * @property float $score
 * @property bool $is_achieved
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\LearningObjective $learningObjective
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereIsAchieved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereLearningObjectiveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentGrade whereUpdatedAt($value)
 */
	class StudentGrade extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $parent_id
 * @property string $type
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string $reason
 * @property string|null $attachment
 * @property string $status
 * @property int|null $approved_by
 * @property string|null $rejection_note
 * @property int|null $study_group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\StudentParent $parent
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\StudyGroup|null $studyGroup
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereRejectionNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereStudyGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentLeave whereUpdatedAt($value)
 */
	class StudentLeave extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $no_whatsapp
 * @property string|null $hubungan
 * @property string|null $father_name
 * @property string|null $mother_name
 * @property string|null $father_occupation
 * @property string|null $mother_occupation
 * @property string|null $address
 * @property string|null $village
 * @property string|null $district
 * @property string|null $city
 * @property string|null $province
 * @property string|null $guardian_name
 * @property string|null $guardian_occupation
 * @property string|null $guardian_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereFatherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereFatherOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereGuardianAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereGuardianName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereGuardianOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereHubungan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereMotherName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereMotherOccupation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereNoWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentParent whereVillage($value)
 */
	class StudentParent extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $academic_year_id
 * @property int $sakit
 * @property int $izin
 * @property int $alpha
 * @property string|null $catatan_wali_kelas
 * @property bool|null $is_naik
 * @property string|null $kenaikan_kelas_to
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereAlpha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereCatatanWaliKelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereIsNaik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereIzin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereKenaikanKelasTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereSakit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentRapor whereUpdatedAt($value)
 */
	class StudentRapor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_rombel
 * @property int $level_id
 * @property int $classroom_id
 * @property int $academic_year_id
 * @property int|null $walikelas_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Classroom $classroom
 * @property-read \App\Models\Level $level
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @property-read \App\Models\Teacher $waliKelas
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereAcademicYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereClassroomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereNamaRombel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudyGroup whereWalikelasId($value)
 */
	class StudyGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_mapel
 * @property string $kode_mapel
 * @property bool $is_umum
 * @property int $total_jp
 * @property int $kkm
 * @property int|null $level_id
 * @property bool $is_one_day_finish
 * @property int $scheduling_priority
 * @property int|null $subject_report_group_id
 * @property bool $is_graded
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Level> $levels
 * @property-read int|null $levels_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \App\Models\SubjectReportGroup|null $subjectReportGroup
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Teacher> $teachers
 * @property-read int|null $teachers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereIsGraded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereIsOneDayFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereIsUmum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereKkm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereKodeMapel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereNamaMapel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereSchedulingPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereSubjectReportGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereTotalJp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subject whereUpdatedAt($value)
 */
	class Subject extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $kelompok
 * @property string $nama_kelompok
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subject> $subjects
 * @property-read int|null $subjects_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup whereKelompok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup whereNamaKelompok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportGroup whereUpdatedAt($value)
 */
	class SubjectReportGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $kurikulum
 * @property int $level_id
 * @property int $subject_id
 * @property string $nama_lokal
 * @property int $no_urut
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Level $level
 * @property-read \App\Models\Subject $subject
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereKurikulum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereNamaLokal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereNoUrut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubjectReportMapping whereUpdatedAt($value)
 */
	class SubjectReportMapping extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string|null $gelar_depan
 * @property string|null $gelar_belakang
 * @property string|null $nip
 * @property string $kode_guru
 * @property bool $is_walikelas
 * @property string $status
 * @property bool $is_kepalasekolah
 * @property string|null $no_whatsapp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $nama_lengkap
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read int|null $grades_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudyGroup> $studyGroups
 * @property-read int|null $study_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subject> $subjects
 * @property-read int|null $subjects_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereGelarBelakang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereGelarDepan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereIsKepalasekolah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereIsWalikelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereKodeGuru($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereNoWhatsapp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Teacher whereUserId($value)
 */
	class Teacher extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $study_group_id
 * @property int $subject_id
 * @property int $teacher_id
 * @property string $hari
 * @property int $start_time_slot_id
 * @property int $end_time_slot_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AcademicYear|null $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\TimeSlot $endTimeSlot
 * @property-read \App\Models\TimeSlot $startTimeSlot
 * @property-read \App\Models\StudyGroup $studyGroup
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereEndTimeSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereStartTimeSlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereStudyGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeacherSchedule whereUpdatedAt($value)
 */
	class TeacherSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama_jam
 * @property \Illuminate\Support\Carbon $waktu_mulai
 * @property \Illuminate\Support\Carbon $waktu_selesai
 * @property bool $is_istirahat
 * @property int $urutan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Level> $levels
 * @property-read int|null $levels_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereIsIstirahat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereNamaJam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereUrutan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereWaktuMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeSlot whereWaktuSelesai($value)
 */
	class TimeSlot extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string|null $email
 * @property string $password
 * @property string|null $photo
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string|null $remember_token
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\StudentParent|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Staff|null $staff
 * @property-read \App\Models\Student|null $student
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudentLeave> $studentLeaves
 * @property-read int|null $student_leaves_count
 * @property-read \App\Models\Teacher|null $teacher
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $teams
 * @property-read int|null $teams_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User team($teams, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTeam($teams)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

