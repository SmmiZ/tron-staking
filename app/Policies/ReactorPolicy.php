<?php

namespace App\Policies;

use App\Models\{Reactor, User};
use Illuminate\Auth\Access\HandlesAuthorization;

class ReactorPolicy
{
    use HandlesAuthorization;

    /**
     * Предварительная проверка доступа
     *
     * @return true|void
     */
    public function before()
    {
        if (auth('staff')->user()?->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return bool
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Reactor $reactor
     * @return bool
     */
    public function view(User $user, Reactor $reactor): bool
    {
        return $user->id === $reactor->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Reactor $reactor
     * @return bool
     */
    public function update(User $user, Reactor $reactor): bool
    {
        return $user->id === $reactor->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Reactor $reactor
     * @return bool
     */
    public function delete(User $user, Reactor $reactor): bool
    {
        return $user->id === $reactor->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Reactor $reactor
     * @return bool
     */
    public function restore(User $user, Reactor $reactor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Reactor $reactor
     * @return bool
     */
    public function forceDelete(User $user, Reactor $reactor): bool
    {
        return false;
    }
}
