<?php

namespace App\Policies;

use App\Models\{Withdrawal, User};
use Illuminate\Auth\Access\HandlesAuthorization;

class WithdrawalPolicy
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
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Withdrawal $withdrawal
     * @return bool
     */
    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $user->id == $withdrawal->user_id;
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
     * @param Withdrawal $withdrawal
     * @return bool
     */
    public function update(User $user, Withdrawal $withdrawal): bool
    {
        return $user->id == $withdrawal->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Withdrawal $withdrawal
     * @return bool
     */
    public function delete(User $user, Withdrawal $withdrawal): bool
    {
        return $user->id == $withdrawal->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Withdrawal $withdrawal
     * @return bool
     */
    public function restore(User $user, Withdrawal $withdrawal): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Withdrawal $withdrawal
     * @return bool
     */
    public function forceDelete(User $user, Withdrawal $withdrawal): bool
    {
        return false;
    }
}
