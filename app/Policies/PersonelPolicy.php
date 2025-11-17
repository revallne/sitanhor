<?php

namespace App\Policies;

use App\Models\Personel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PersonelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Personel $personel): bool
    {
        return auth()->user()->hasRole(['bagwatpers', 'personel']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
         return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Personel $personel): bool
    {
        return auth()->user()->hasRole(['bagwatpers', 'personel']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Personel $personel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Personel $personel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Personel $personel): bool
    {
        return false;
    }
}
