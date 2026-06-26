<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Classroom;
use App\Models\StudentParent;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Username')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->maxLength(255),
                Select::make('selected_role')
                    ->label('Role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'staff' => 'Staff',
                        'guru' => 'Guru',
                        'siswa' => 'Siswa',
                        'wali' => 'Orang Tua / Wali',
                    ])
                    ->required()
                    ->live(),

                // Data Guru
                TextInput::make('teacher_nip')
                    ->label('NIP')
                    ->required(fn (Get $get): bool => $get('selected_role') === 'guru')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'guru')
                    ->maxLength(20),
                TextInput::make('teacher_no_whatsapp')
                    ->label('No. WhatsApp Guru')
                    ->tel()
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'guru')
                    ->maxLength(20),
                Toggle::make('teacher_is_walikelas')
                    ->label('Wali Kelas')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'guru'),
                Toggle::make('teacher_is_kepalasekolah')
                    ->label('Kepala Sekolah')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'guru'),

                // Data Staff
                TextInput::make('staff_jabatan')
                    ->label('Jabatan Staff')
                    ->required(fn (Get $get): bool => $get('selected_role') === 'staff')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'staff')
                    ->maxLength(50),
                TextInput::make('staff_no_whatsapp')
                    ->label('No. WhatsApp Staff')
                    ->tel()
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'staff')
                    ->maxLength(20),

                // Data Siswa
                TextInput::make('student_nisn')
                    ->label('NISN Siswa')
                    ->required(fn (Get $get): bool => $get('selected_role') === 'siswa')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'siswa')
                    ->maxLength(10),
                Select::make('student_classroom_id')
                    ->label('Kelas Siswa')
                    ->options(fn () => Classroom::pluck('nama_kelas', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get): bool => $get('selected_role') === 'siswa')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'siswa'),
                Select::make('student_parent_id')
                    ->label('Orang Tua / Wali')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => StudentParent::query()
                        ->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('mother_name', 'like', "%{$search}%")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn ($parent) => [$parent->id => ($parent->user?->name ?? $parent->father_name ?? 'Tanpa Nama')])
                        ->toArray()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => StudentParent::with('user')->find($value)?->user?->name ?? StudentParent::find($value)?->father_name)
                    ->required(fn (Get $get): bool => $get('selected_role') === 'siswa')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'siswa'),

                // Data Orang Tua
                Select::make('parent_hubungan')
                    ->label('Hubungan Orang Tua')
                    ->options([
                        'ayah' => 'Ayah',
                        'ibu' => 'Ibu',
                        'wali' => 'Wali/Lainnya',
                    ])
                    ->required(fn (Get $get): bool => $get('selected_role') === 'wali')
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'wali'),
                TextInput::make('parent_no_whatsapp')
                    ->label('No. WhatsApp Orang Tua')
                    ->tel()
                    ->visible(fn (Get $get): bool => $get('selected_role') === 'wali')
                    ->maxLength(20),
            ]);
    }
}
