<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ChatbotRequestLog;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ChatbotRequestLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ChatbotRequestLog');
    }

    public function view(AuthUser $authUser, ChatbotRequestLog $chatbotRequestLog): bool
    {
        return $authUser->can('View:ChatbotRequestLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ChatbotRequestLog');
    }

    public function update(AuthUser $authUser, ChatbotRequestLog $chatbotRequestLog): bool
    {
        return $authUser->can('Update:ChatbotRequestLog');
    }

    public function delete(AuthUser $authUser, ChatbotRequestLog $chatbotRequestLog): bool
    {
        return $authUser->can('Delete:ChatbotRequestLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ChatbotRequestLog');
    }

    public function restore(AuthUser $authUser, ChatbotRequestLog $chatbotRequestLog): bool
    {
        return $authUser->can('Restore:ChatbotRequestLog');
    }

    public function forceDelete(AuthUser $authUser, ChatbotRequestLog $chatbotRequestLog): bool
    {
        return $authUser->can('ForceDelete:ChatbotRequestLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ChatbotRequestLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ChatbotRequestLog');
    }

    public function replicate(AuthUser $authUser, ChatbotRequestLog $chatbotRequestLog): bool
    {
        return $authUser->can('Replicate:ChatbotRequestLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ChatbotRequestLog');
    }
}
