<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $jabatan
 * @property string $status
 * @property string|null $no_whatsapp
 */
#[Table('staffs')]
#[Fillable([
    'user_id',
    'jabatan',
    'status',
    'no_whatsapp',
])]
class Staff extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\ActiveScope);

        static::updated(function ($staff) {
            // Auto-sync is_active on User model
            if ($staff->isDirty('status') && $staff->user) {
                $isActive = $staff->status === 'aktif';
                $staff->user->update(['is_active' => $isActive]);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
