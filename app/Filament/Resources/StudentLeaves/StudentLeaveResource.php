<?php

namespace App\Filament\Resources\StudentLeaves;

use App\Filament\Resources\StudentLeaves\Pages;
use App\Models\StudentLeave;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use UnitEnum;
use BackedEnum;

class StudentLeaveResource extends Resource
{
    protected static ?string $model = StudentLeave::class;

    protected static ?string $recordTitleAttribute = 'reason';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Izin Siswa';

    protected static ?string $modelLabel = 'Izin Siswa';

    protected static ?string $pluralModelLabel = 'Izin Siswa';

    protected static UnitEnum|string|null $navigationGroup = 'Akademik & KBM';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Izin')
                    ->schema([
                        Select::make('student_id')
                            ->relationship('student.user', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Siswa')
                            ->required(fn (string $context) => $context === 'create')
                            ->disabled(fn (string $context) => $context !== 'create'),
                        Select::make('type')
                            ->options([
                                'sakit' => 'Sakit',
                                'izin' => 'Izin',
                            ])
                            ->label('Tipe')
                            ->required(fn (string $context) => $context === 'create')
                            ->disabled(fn (string $context) => $context !== 'create'),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(fn (string $context) => $context === 'create')
                            ->disabled(fn (string $context) => $context !== 'create'),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->afterOrEqual('start_date')
                            ->required(fn (string $context) => $context === 'create')
                            ->disabled(fn (string $context) => $context !== 'create'),
                        Textarea::make('reason')
                            ->label('Alasan')
                            ->minLength(10)
                            ->required(fn (string $context) => $context === 'create')
                            ->disabled(fn (string $context) => $context !== 'create')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\ViewField::make('attachment_preview')
                            ->label('Bukti Lampiran')
                            ->view('filament.components.image-preview')
                            ->visible(fn (string $context) => $context !== 'create')
                            ->columnSpanFull(),
                        FileUpload::make('attachment')
                            ->label('Lampiran')
                            ->image()
                            ->disk('public')
                            ->directory('leaves')
                            ->visible(fn (string $context) => $context === 'create')
                            ->columnSpanFull(),
                    ])->columns(2),
                
                Section::make('Keputusan Admin')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Tertunda',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('approved')
                            ->required()
                            ->native(false),
                        Textarea::make('rejection_note')
                            ->label('Catatan Penolakan')
                            ->visible(fn ($get) => $get('status') === 'rejected'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
                TextColumn::make('student.user.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('studyGroup.nama_rombel')
                    ->label('Rombel')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('attachment')
                    ->label('Bukti')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sakit' => 'warning',
                        'izin' => 'info',
                    }),
                TextColumn::make('start_date')
                    ->label('Dari')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Sampai')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
        ];

        $filters = [
                Tables\Filters\SelectFilter::make('academic_year')
                    ->label('Tahun Ajaran')
                    ->options(fn () => \App\Models\AcademicYear::query()
                        ->get()
                        ->mapWithKeys(fn ($year) => [$year->id => "{$year->tahun_ajaran} - " . ucfirst($year->semester)])
                    )
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('studyGroup', function ($q) use ($data) {
                            $q->where('study_groups.academic_year_id', $data['value']);
                        });
                    })
                    ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Tertunda',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('study_group_id')
                    ->relationship('studyGroup', 'nama_rombel', function ($query, $livewire) {
                        $academicYearId = $livewire->tableFilters['academic_year']['value'] ?? null;
                        $academicYearId = $academicYearId ?: \App\Models\AcademicYear::where('is_active', true)->first()?->id;
                        if ($academicYearId) {
                            return $query->where('academic_year_id', $academicYearId);
                        }
                        return $query;
                    })
                    ->label('Rombel'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    })
        ];

        $actions = [
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function (StudentLeave $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                        ]);

                        // SYNC TO ATTENDANCE
                        self::syncToAttendance($record);

                        Notification::make()
                            ->title('Izin Disetujui')
                            ->success()
                            ->send();

                        // Notify Parent via WA
                        if ($record->parent && $record->parent->no_whatsapp) {
                            $studentName = $record->student->user->name;
                            $schoolName = strtoupper(\App\Models\SchoolSetting::current()->name);
                            $startDate = \Illuminate\Support\Carbon::parse($record->start_date)->format('d/m/Y');
                            
                            $message = "*PEMBERITAHUAN IZIN - $schoolName*\n\n";
                            $message .= "Yth. Orang Tua dari *$studentName*,\n\n";
                            $message .= "Permohonan izin untuk tanggal *$startDate* telah *DISETUJUI*.\n";
                            $message .= "Status presensi siswa otomatis diperbarui di sistem.\n\n";
                            $message .= "Terima kasih.\n";
                            $message .= "--- _Powered by Aksara_ ---";

                            \App\Services\WAService::sendMessageAsync($record->parent->no_whatsapp, $message);
                        }
                    }),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_note')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function (StudentLeave $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_note' => $data['rejection_note'],
                            'approved_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Izin Ditolak')
                            ->danger()
                            ->send();

                        // Notify Parent via WA
                        if ($record->parent && $record->parent->no_whatsapp) {
                            $studentName = $record->student->user->name;
                            $schoolName = strtoupper(\App\Models\SchoolSetting::current()->name);
                            $startDate = \Illuminate\Support\Carbon::parse($record->start_date)->format('d/m/Y');
                            
                            $message = "*PEMBERITAHUAN IZIN - $schoolName*\n\n";
                            $message .= "Yth. Orang Tua dari *$studentName*,\n\n";
                            $message .= "Permohonan izin untuk tanggal *$startDate* telah *DITOLAK*.\n\n";
                            $message .= "*Alasan:* " . $data['rejection_note'] . "\n\n";
                            $message .= "Silakan tinjau kembali melalui portal orang tua.\n\n";
                            $message .= "Terima kasih.\n";
                            $message .= "--- _Powered by Aksara_ ---";

                            \App\Services\WAService::sendMessageAsync($record->parent->no_whatsapp, $message);
                        }
                    }),
        ];

        $bulkActions = [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions($actions)
            ->bulkActions($bulkActions);
    }

    /**
     * Sync approved leave to attendance records.
     */
    public static function syncToAttendance(StudentLeave $leave)
    {
        $startDate = $leave->start_date;
        $endDate = $leave->end_date;
        $studentId = $leave->student_id;
        $status = $leave->type === 'sakit' ? 'sakit' : 'izin';

        // Fallback to student's current rombel if not set in leave request
        $rombelId = $leave->study_group_id ?? $leave->student->currentStudyGroup()?->id;

        $current = \Illuminate\Support\Carbon::parse($startDate)->copy();
        while ($current->lte($endDate)) {
            \App\Models\Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'study_group_id' => $rombelId,
                    'tanggal' => $current->format('Y-m-d'),
                ],
                [
                    'status' => $status,
                    'catatan' => 'Otomatis dari pengajuan izin: ' . $leave->reason,
                ]
            );
            $current->addDay();
        }
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasRole('guru') && $user->teacher) {
            $query->whereHas('studyGroup', function ($q) use ($user) {
                $q->where('walikelas_id', $user->teacher->id);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStudentLeaves::route('/'),
        ];
    }
}
