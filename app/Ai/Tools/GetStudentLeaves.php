<?php

namespace App\Ai\Tools;

use App\Models\StudentLeave;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetStudentLeaves implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengecek data dan status pengajuan cuti/izin/sakit siswa.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (!$this->user) {
            return 'Error: User context missing.';
        }

        $studentId = $request['student_id'] ?? null;
        $roleName = $this->user->roles->first()?->name ?? 'siswa';

        $query = StudentLeave::query()->with(['student.user', 'approvedBy']);

        if (str_contains($roleName, 'siswa')) {
            $student = $this->user->student;
            if (!$student) return 'Data siswa tidak ditemukan.';
            $query->where('student_id', $student->id);
        } elseif (str_contains($roleName, 'orang_tua') || str_contains($roleName, 'wali')) {
            $parent = $this->user->parent;
            if (!$parent) return 'Data orang tua tidak ditemukan.';
            $childIds = $parent->students->pluck('id')->toArray();
            
            if ($studentId && in_array($studentId, $childIds)) {
                $query->where('student_id', $studentId);
            } else {
                $query->whereIn('student_id', $childIds);
            }
        } elseif ($studentId) {
            $query->where('student_id', $studentId);
        }

        // Limit results to last 20 requests
        $leaves = $query->latest()->limit(20)->get();

        if ($leaves->isEmpty()) {
            return 'Tidak ada data pengajuan cuti/izin/sakit.';
        }

        $result = $leaves->map(function ($l) {
            /** @var StudentLeave $l */
            return [
                'nama_siswa' => $l->student?->user?->name,
                'jenis_izin' => ucfirst($l->type),
                'mulai' => $l->start_date->format('d M Y'),
                'selesai' => $l->end_date->format('d M Y'),
                'alasan' => $l->reason,
                'status' => strtoupper($l->status),
                'disetujui_oleh' => $l->approvedBy?->name ?? '-',
                'catatan_penolakan' => $l->rejection_note ?? '-',
            ];
        });

        return json_encode([
            'total_data' => $leaves->count(),
            'pengajuan_izin' => $result->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'student_id' => $schema->integer()->description('Opsional. ID Siswa.'),
        ];
    }
}
