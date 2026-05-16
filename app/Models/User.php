<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string|null $email
 * @property string $password
 * @property string|null $photo
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 */
#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'photo',
    'is_active',
])]
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;
    
    protected $guard_name = 'web';

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Users table only has created_at, no updated_at
    const UPDATED_AT = null;

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function parent()
    {
        return $this->hasOne(StudentParent::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function studentLeaves()
    {
        return $this->hasMany(StudentLeave::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }
}
