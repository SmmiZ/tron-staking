<?php

namespace App\Policies;

use App\Models\{Order, Staff};
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
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
     * @param Order $order
     * @return bool
     */
    public function view(Staff $staff, Order $order): bool
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
     * @param Order $order
     * @return bool
     */
    public function update(Staff $staff, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param Staff $staff
     * @param Order $order
     * @return bool
     */
    public function delete(Staff $staff, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param Staff $staff
     * @param Order $order
     * @return bool
     */
    public function restore(Staff $staff, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param Staff $staff
     * @param Order $order
     * @return bool
     */
    public function forceDelete(Staff $staff, Order $order): bool
    {
        return false;
    }
}
