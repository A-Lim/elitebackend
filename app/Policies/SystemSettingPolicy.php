<?php

namespace App\Policies;

use App\User;
use App\SystemSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class SystemSettingPolicy {
    use HandlesAuthorization;

    /**
     * Bypass any policy
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function before(User $user, $ability) {
        if ($user->isAdmin())
            return true;
    }

    /**
     * Determine whether the user can view any user groups.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user) {
        return $user->can('systemsettings.viewAny');
    }

    /**
     * Determine whether the user can update systemsettings.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $user) {
        return $user->can('systemsettings.update');
    }
}
