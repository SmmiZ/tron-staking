<?php

namespace App\Policies;

use App\Models\{Consumer, Staff};
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsumerPolicy
{
    use HandlesAuthorization;

    /**
     * Предварительная проверка доступа
     *
     * @param Staff $staff
     * @param $ability
     * @return true|void
     */
    public function before(Staff $staff, $ability)
    {
        if ($staff->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param Staff $staff
     * @return bool
     */
    public function viewAny(Staff $staff): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param Staff $staff
     * @param Consumer $consumer
     * @return bool
     */
    public function view(Staff $staff, Consumer $consumer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param Staff $staff
     * @return bool
     */
    public function create(Staff $staff): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param Staff $staff
     * @param Consumer $consumer
     * @return bool
     */
    public function update(Staff $staff, Consumer $consumer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Staff $staff
     * @param Consumer $consumer
     * @return bool
     */
    public function delete(Staff $staff, Consumer $consumer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Staff $staff
     * @param Consumer $consumer
     * @return bool
     */
    public function restore(Staff $staff, Consumer $consumer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Staff $staff
     * @param Consumer $consumer
     * @return bool
     */
    public function forceDelete(Staff $staff, Consumer $consumer): bool
    {
        return false;
    }
}
