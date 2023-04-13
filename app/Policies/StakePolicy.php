<?php

namespace App\Policies;

use App\Models\{Stake, User};
use Illuminate\Auth\Access\HandlesAuthorization;

class StakePolicy
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
     * @param Stake $stake
     * @return bool
     */
    public function view(User $user, Stake $stake): bool
    {
        return $user->id === $stake->user_id;
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
     * @param Stake $stake
     * @return bool
     */
    public function update(User $user, Stake $stake): bool
    {
        return $user->id === $stake->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Stake $stake
     * @return bool
     */
    public function delete(User $user, Stake $stake): bool
    {
        return $user->id === $stake->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Stake $stake
     * @return bool
     */
    public function restore(User $user, Stake $stake): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Stake $stake
     * @return bool
     */
    public function forceDelete(User $user, Stake $stake): bool
    {
        return false;
    }
}
