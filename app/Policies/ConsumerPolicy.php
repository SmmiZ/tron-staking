<?php

namespace App\Policies;

use App\Models\{Consumer, User};
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsumerPolicy
{
    use HandlesAuthorization;

    /**
     * Предварительная проверка доступа
     *
     * @param User $user
     * @param $ability
     * @return true|void
     */
    public function before(User $user, $ability)
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
     * @param Consumer $consumer
     * @return bool
     */
    public function view(User $user, Consumer $consumer): bool
    {
        return $user->id == $consumer->user_id;
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
     * @param Consumer $consumer
     * @return bool
     */
    public function update(User $user, Consumer $consumer): bool
    {
        return $user->id == $consumer->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Consumer $consumer
     * @return bool
     */
    public function delete(User $user, Consumer $consumer): bool
    {
        return $user->id == $consumer->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Consumer $consumer
     * @return bool
     */
    public function restore(User $user, Consumer $consumer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Consumer $consumer
     * @return bool
     */
    public function forceDelete(User $user, Consumer $consumer): bool
    {
        return false;
    }


    /**
     * Determine whether the user can pay for the model.
     *
     * @param User $user
     * @param Consumer $consumer
     * @return bool
     */
    public function payConsumer(User $user, Consumer $consumer): bool
    {
        return $user->id == $consumer->user_id;
    }
}
