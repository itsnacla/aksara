<?php

namespace App\Filament\Pages;

use App\Jobs\SendWhatsAppBroadcast;
use App\Models\SchoolSetting;
use App\Models\StudentParent;
use App\Models\StudyGroup;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class ManageWhatsApp extends Page implements HasForms
{
    use HasPageShield, InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.manage-whats-app';

    protected static ?string $navigationLabel = 'WA Notifikasi';

    protected static ?string $title = 'Pengaturan WhatsApp Notifikasi';

    protected static ?string $slug = 'manage-whats-app';

    protected static UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public ?array $broadcastData = [];

    public string $activeTab = 'settings';

    public function mount(): void
    {
        $settings = SchoolSetting::current();
        $this->settingsForm->fill($settings->toArray());
        $this->broadcastForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'settingsForm',
            'broadcastForm',
        ];
    }

    public function settingsForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Section::make('Status Layanan')
                    ->schema([
                        Toggle::make('is_wa_enabled')
                            ->label('Aktifkan Layanan WhatsApp')
                            ->helperText('Aktifkan saklar ini untuk menjalankan semua fitur integrasi WhatsApp.')
                            ->live(),
                    ]),

                Section::make('Notifikasi Otomatis')
                    ->visible(fn ($get) => $get('is_wa_enabled'))
                    ->schema([
                        Toggle::make('wa_notify_attendance')
                            ->label('Notifikasi Presensi QR')
                            ->helperText('Kirim WA ke orang tua saat siswa melakukan scan QR presensi.'),
                        Toggle::make('wa_notify_announcement')
                            ->label('Notifikasi Pengumuman')
                            ->helperText('Aktifkan pengiriman pengumuman otomatis jika ada.'),
                    ])
                    ->columns(2),

                Section::make('Konfigurasi Gateway')
                    ->visible(fn ($get) => $get('is_wa_enabled'))
                    ->schema([
                        Select::make('wa_gateway_provider')
                            ->label('Provider / Penyedia Layanan')
                            ->options([
                                'fonnte' => 'Fonnte (Rekomendasi)',
                                'custom' => 'Custom API Gateway',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('wa_gateway_url')
                            ->label('API Endpoint URL')
                            ->placeholder('https://api.fonnte.com/send')
                            ->required()
                            ->visible(fn ($get) => $get('wa_gateway_provider') === 'custom')
                            ->url(),

                        TextInput::make('wa_gateway_token')
                            ->label('API Token / Key')
                            ->password()
                            ->revealable()
                            ->required(),

                        TextInput::make('wa_gateway_phone_param')
                            ->label('Parameter Nama (Nomor HP)')
                            ->placeholder('target')
                            ->default('target')
                            ->required()
                            ->visible(fn ($get) => $get('wa_gateway_provider') === 'custom'),

                        TextInput::make('wa_gateway_message_param')
                            ->label('Parameter Nama (Pesan)')
                            ->placeholder('message')
                            ->default('message')
                            ->required()
                            ->visible(fn ($get) => $get('wa_gateway_provider') === 'custom'),
                    ])
                    ->columns(2),
            ]);
    }

    public function broadcastForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('broadcastData')
            ->schema([
                Section::make('Broadcast Pengumuman')
                    ->description('Kirim pesan massal ke orang tua siswa.')
                    ->schema([
                        Select::make('target_type')
                            ->label('Target Penerima')
                            ->options([
                                'all' => 'Semua Orang Tua',
                                'rombel' => 'Berdasarkan Rombel',
                            ])
                            ->required()
                            ->live()
                            ->default('all'),

                        Select::make('study_group_id')
                            ->label('Pilih Rombel')
                            ->options(StudyGroup::pluck('nama_rombel', 'id'))
                            ->required()
                            ->visible(fn ($get) => $get('target_type') === 'rombel'),

                        Textarea::make('message')
                            ->label('Isi Pengumuman')
                            ->placeholder('Tulis pengumuman di sini...')
                            ->rows(5)
                            ->required()
                            ->helperText('Gunakan format *teks* untuk tebal, _teks_ untuk miring.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $settings = SchoolSetting::current();
        $settings->update($this->settingsForm->getState());

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    public function sendBroadcast(): void
    {
        $data = $this->broadcastForm->getState();
        $settings = SchoolSetting::current();

        // 1. Check if WA is enabled
        if (! $settings->is_wa_enabled) {
            Notification::make()
                ->title('Gagal: Layanan WA belum aktif')
                ->body('Silakan aktifkan layanan di tab Pengaturan Gateway terlebih dahulu.')
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        // 2. Fetch target parents
        $query = StudentParent::query()
            ->whereNotNull('no_whatsapp')
            ->where('no_whatsapp', '!=', '');

        if ($data['target_type'] === 'rombel') {
            $query->whereHas('students.studyGroups', function ($q) use ($data) {
                $q->where('study_groups.id', $data['study_group_id']);
            });
        }

        $parents = $query->get();

        // 3. Handle empty targets
        if ($parents->isEmpty()) {
            Notification::make()
                ->title('Gagal: Target tidak ditemukan')
                ->body('Tidak ada orang tua dengan nomor WhatsApp valid pada target yang dipilih.')
                ->warning()
                ->send();

            return;
        }

        // 4. Dispatch jobs with progressive delay to avoid "Account Restricted"
        $count = 0;
        $delaySeconds = 0;
        foreach ($parents as $parent) {
            SendWhatsAppBroadcast::dispatch($parent->no_whatsapp, $data['message'])
                ->delay(now()->addSeconds($delaySeconds));

            $delaySeconds += 5; // Increment delay by 5 seconds per recipient
            $count++;
        }

        // 5. Success feedback
        Notification::make()
            ->title('Berhasil!')
            ->body("Pengumuman sedang dikirim ke $count orang tua siswa.")
            ->success()
            ->send();

        $this->broadcastForm->fill();
    }
}
