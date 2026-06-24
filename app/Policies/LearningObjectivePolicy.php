<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningObjective;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningObjectivePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningObjective');
    }

    public function view(AuthUser $authUser, LearningObjective $learningObjective): bool
    {
        return $authUser->can('View:LearningObjective');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningObjective');
    }

    public function update(AuthUser $authUser, LearningObjective $learningObjective): bool
    {
        return $authUser->can('Update:LearningObjective');
    }

    public function delete(AuthUser $authUser, LearningObjective $learningObjective): bool
    {
        return $authUser->can('Delete:LearningObjective');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LearningObjective');
    }

    public function restore(AuthUser $authUser, LearningObjective $learningObjective): bool
    {
        return $authUser->can('Restore:LearningObjective');
    }

    public function forceDelete(AuthUser $authUser, LearningObjective $learningObjective): bool
    {
        return $authUser->can('ForceDelete:LearningObjective');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningObjective');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningObjective');
    }

    public function replicate(AuthUser $authUser, LearningObjective $learningObjective): bool
    {
        return $authUser->can('Replicate:LearningObjective');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningObjective');
    }

}