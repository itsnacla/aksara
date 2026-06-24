<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\User;

class LoginAs extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $title = 'Login As (Switch User)';
    protected static ?string $slug = 'login-as';
    protected string $view = 'filament.pages.login-as';
    protected static ?string $navigationLabel = 'Login As';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('Impersonate') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('id', '!=', auth()->id())
                    ->with('roles')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Peran (Role)')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'guru', 'teacher' => 'success',
                        'siswa' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match(strtolower($state)) {
                        'siswa' => '🎓 Siswa',
                        'orang_tua', 'wali' => '👥 Orang Tua',
                        'guru', 'teacher' => '👨‍🏫 Guru',
                        'staff' => '⚙️ Staff',
                        'super_admin' => '👑 Super Admin',
                        'admin' => '🛡️ Admin',
                        default => $state
                    })
                    ->limit(1),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Filter Peran (Role)')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Action::make('login_as')
                    ->label('Login As')
                    ->button()
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Impersonasi')
                    ->modalDescription('Apakah Anda yakin ingin masuk sebagai pengguna ini? Sesi Anda akan beralih sementara.')
                    ->action(function (User $record) {
                        if (!\Illuminate\Support\Facades\Gate::allows('impersonate', $record)) {
                            abort(403, 'Hanya Super Admin yang dapat menggunakan fitur Login As.');
                        }

                        $currentUser = auth()->user();
                        if (!$currentUser) {
                            return;
                        }

                        // Save original admin user ID in session
                        session(['impersonator_id' => $currentUser->id]);

                        // Login as the target user
                        \Illuminate\Support\Facades\Auth::login($record);

                        // Redirect based on target user role
                        $targetRole = strtolower($record->roles->first()?->name ?? '');
                        if (in_array($targetRole, ['siswa', 'orang_tua', 'wali', 'parent'])) {
                            return redirect()->to('/dashboard')->with('success', "Berhasil masuk sebagai {$record->name}.");
                        } else {
                            return redirect()->to('/admin')->with('success', "Berhasil masuk sebagai {$record->name}.");
                        }
                    }),
            ])
            ->paginated([10, 25, 50])
            ->striped()
            ->emptyStateHeading('Tidak ada pengguna')
            ->emptyStateDescription('Semua pengguna sudah Anda impersonasi atau tidak ada pengguna lain.');
    }
}
