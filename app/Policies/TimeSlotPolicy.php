<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TimeSlot;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimeSlotPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TimeSlot');
    }

    public function view(AuthUser $authUser, TimeSlot $timeSlot): bool
    {
        return $authUser->can('View:TimeSlot');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TimeSlot');
    }

    public function update(AuthUser $authUser, TimeSlot $timeSlot): bool
    {
        return $authUser->can('Update:TimeSlot');
    }

    public function delete(AuthUser $authUser, TimeSlot $timeSlot): bool
    {
        return $authUser->can('Delete:TimeSlot');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TimeSlot');
    }

    public function restore(AuthUser $authUser, TimeSlot $timeSlot): bool
    {
        return $authUser->can('Restore:TimeSlot');
    }

    public function forceDelete(AuthUser $authUser, TimeSlot $timeSlot): bool
    {
        return $authUser->can('ForceDelete:TimeSlot');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TimeSlot');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TimeSlot');
    }

    public function replicate(AuthUser $authUser, TimeSlot $timeSlot): bool
    {
        return $authUser->can('Replicate:TimeSlot');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TimeSlot');
    }

}