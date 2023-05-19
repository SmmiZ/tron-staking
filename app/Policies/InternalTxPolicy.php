<?php

namespace App\Policies;

use App\Models\{Staff, InternalTx};

class InternalTxPolicy
{
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
     */
    public function viewAny(Staff $staff): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Staff $staff, InternalTx $transaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Staff $staff): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Staff $staff, InternalTx $transaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Staff $staff, InternalTx $transaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Staff $staff, InternalTx $transaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Staff $staff, InternalTx $transaction): bool
    {
        return false;
    }
}
