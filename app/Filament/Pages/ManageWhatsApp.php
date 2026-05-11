<?php

namespace App\Filament\Pages;

use App\Models\SchoolSetting;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use App\Models\StudentParent;
use App\Models\StudyGroup;
use App\Jobs\SendWhatsAppBroadcast;
use UnitEnum;
use BackedEnum;

class ManageWhatsApp extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected string $view = 'filament.pages.manage-whats-app';

    protected static ?string $navigationLabel = 'WA Notifikasi';

    protected static ?string $title = 'Pengaturan WhatsApp Notifikasi';

    protected static ?string $slug = 'manage-whats-app';

    protected static UnitEnum|string|null $navigationGroup = 'Manajemen Sekolah';

    protected static ?int $navigationSort = 10;

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
                            ->label('Aktifkan Notifikasi WA')
                            ->helperText('Jika aktif, sistem akan mengirim pesan otomatis ke wali murid setiap kali siswa melakukan scan.')
                            ->live(),
                    ]),

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

        if (!$settings->is_wa_enabled) {
            Notification::make()
                ->title('Gagal: Layanan WA belum aktif')
                ->danger()
                ->send();
            return;
        }

        $parents = [];
        if ($data['target_type'] === 'all') {
            $parents = StudentParent::whereNotNull('no_whatsapp')->get();
        } else {
            $parents = StudentParent::whereHas('students.studyGroups', function ($q) use ($data) {
                $q->where('study_groups.id', $data['study_group_id']);
            })->whereNotNull('no_whatsapp')->get();
        }

        if ($parents->isEmpty()) {
            Notification::make()
                ->title('Gagal: Tidak ada nomor WA ditemukan')
                ->warning()
                ->send();
            return;
        }

        foreach ($parents as $parent) {
            SendWhatsAppBroadcast::dispatch($parent->no_whatsapp, $data['message']);
        }

        Notification::make()
            ->title('Broadcast dikirim ke ' . $parents->count() . ' orang tua')
            ->success()
            ->send();

        $this->broadcastForm->fill();
    }
}
