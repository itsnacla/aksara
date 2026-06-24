<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\P5Theme;
use Illuminate\Auth\Access\HandlesAuthorization;

class P5ThemePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:P5Theme');
    }

    public function view(AuthUser $authUser, P5Theme $p5Theme): bool
    {
        return $authUser->can('View:P5Theme');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:P5Theme');
    }

    public function update(AuthUser $authUser, P5Theme $p5Theme): bool
    {
        return $authUser->can('Update:P5Theme');
    }

    public function delete(AuthUser $authUser, P5Theme $p5Theme): bool
    {
        return $authUser->can('Delete:P5Theme');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:P5Theme');
    }

    public function restore(AuthUser $authUser, P5Theme $p5Theme): bool
    {
        return $authUser->can('Restore:P5Theme');
    }

    public function forceDelete(AuthUser $authUser, P5Theme $p5Theme): bool
    {
        return $authUser->can('ForceDelete:P5Theme');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:P5Theme');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:P5Theme');
    }

    public function replicate(AuthUser $authUser, P5Theme $p5Theme): bool
    {
        return $authUser->can('Replicate:P5Theme');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:P5Theme');
    }

}