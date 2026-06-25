<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SubjectReportGroup;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SubjectReportGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SubjectReportGroup');
    }

    public function view(AuthUser $authUser, SubjectReportGroup $subjectReportGroup): bool
    {
        return $authUser->can('View:SubjectReportGroup');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SubjectReportGroup');
    }

    public function update(AuthUser $authUser, SubjectReportGroup $subjectReportGroup): bool
    {
        return $authUser->can('Update:SubjectReportGroup');
    }

    public function delete(AuthUser $authUser, SubjectReportGroup $subjectReportGroup): bool
    {
        return $authUser->can('Delete:SubjectReportGroup');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SubjectReportGroup');
    }

    public function restore(AuthUser $authUser, SubjectReportGroup $subjectReportGroup): bool
    {
        return $authUser->can('Restore:SubjectReportGroup');
    }

    public function forceDelete(AuthUser $authUser, SubjectReportGroup $subjectReportGroup): bool
    {
        return $authUser->can('ForceDelete:SubjectReportGroup');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SubjectReportGroup');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SubjectReportGroup');
    }

    public function replicate(AuthUser $authUser, SubjectReportGroup $subjectReportGroup): bool
    {
        return $authUser->can('Replicate:SubjectReportGroup');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SubjectReportGroup');
    }
}
