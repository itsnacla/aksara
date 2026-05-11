<?php

namespace App\Ai\Tools;

use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetStudentDetails implements Tool
{
    public function __construct(protected ?User $user = null) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Gunakan tool ini untuk mendapatkan informasi detail tentang siswa berdasarkan NISN atau Nama.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Student::query()->with(['user', 'studyGroup.level', 'parent.user']);

        // Mengambil nilai dari Request secara aman
        $nisn = $request['nisn'] ?? null;
        $name = $request['name'] ?? null;

        if ($nisn) {
            $query->where('nisn', $nisn);
        } elseif ($name) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$name}%"));
        } else {
            return 'Mohon berikan NISN atau Nama siswa.';
        }

        $student = $query->first();

        if (!$student) {
            return 'Siswa tidak ditemukan.';
        }

        return json_encode([
            'nama' => $student->user->name,
            'nisn' => $student->nisn,
            'rombel' => $student->studyGroup?->nama_rombel,
            'tingkatan' => $student->studyGroup?->level?->nama_tingkatan,
            'orang_tua' => $student->parent?->user?->name,
            'email' => $student->user->email,
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'nisn' => $schema->string()->description('Nomor Induk Siswa Nasional (10 digit)'),
            'name' => $schema->string()->description('Nama lengkap atau potongan nama siswa'),
        ];
    }
}
