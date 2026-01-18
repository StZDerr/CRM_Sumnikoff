<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        // Любой авторизованный пользователь может видеть список (фильтрация на контроллере)
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        // Admin и Project Manager могут смотреть любой проект
        if ($user->isAdmin() || $user->isProjectManager()) {
            return true;
        }

        // Маркетолог может смотреть только проекты, где он назначен
        if ($user->isMarketer() && $project->marketer_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isProjectManager();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->isProjectManager();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->isProjectManager();
    }
}
