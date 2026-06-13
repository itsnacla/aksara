<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WhatsAppLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class WhatsAppLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WhatsAppLog');
    }

    public function view(AuthUser $authUser, WhatsAppLog $whatsAppLog): bool
    {
        return $authUser->can('View:WhatsAppLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WhatsAppLog');
    }

    public function update(AuthUser $authUser, WhatsAppLog $whatsAppLog): bool
    {
        return $authUser->can('Update:WhatsAppLog');
    }

    public function delete(AuthUser $authUser, WhatsAppLog $whatsAppLog): bool
    {
        return $authUser->can('Delete:WhatsAppLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:WhatsAppLog');
    }

    public function restore(AuthUser $authUser, WhatsAppLog $whatsAppLog): bool
    {
        return $authUser->can('Restore:WhatsAppLog');
    }

    public function forceDelete(AuthUser $authUser, WhatsAppLog $whatsAppLog): bool
    {
        return $authUser->can('ForceDelete:WhatsAppLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WhatsAppLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WhatsAppLog');
    }

    public function replicate(AuthUser $authUser, WhatsAppLog $whatsAppLog): bool
    {
        return $authUser->can('Replicate:WhatsAppLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WhatsAppLog');
    }

}
