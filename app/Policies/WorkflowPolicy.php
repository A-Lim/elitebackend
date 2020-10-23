<?php

namespace App\Policies;

use App\User;
use App\Workflow;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkflowPolicy {
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
     * Determine whether the user can view any workflows.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user) {
        return $user->can('workflows.viewAny');
    }

    /**
     * Determine whether the user can view the workflow.
     *
     * @param  \App\User  $user
     * @param  \App\Workflow  $workflows
     * @return mixed
     */
    public function view(User $user, Workflow $workflows) {
        return $user->can('workflows.view');
    }

    /**
     * Determine whether the user can create workflows.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user) {
        return $user->can('workflows.create');
    }

    /**
     * Determine whether the user can update the workflow.
     *
     * @param  \App\User  $user
     * @param  \App\Workflow  $workflows
     * @return mixed
     */
    public function update(User $user, Workflow $workflows) {
        return $user->can('workflows.update');
    }

    /**
     * Determine whether the user can delete the workflow.
     *
     * @param  \App\User  $user
     * @param  \App\Workflow  $workflows
     * @return mixed
     */
    public function delete(User $user, Workflow $workflows) {
        return $user->can('workflows.delete');
    }

    /**
     * Determine whether the user can restore the workflow.
     *
     * @param  \App\User  $user
     * @param  \App\Workflow  $workflows
     * @return mixed
     */
    public function restore(User $user, Workflow $workflows) {
        //
    }

    /**
     * Determine whether the user can permanently delete the workflow.
     *
     * @param  \App\User  $user
     * @param  \App\Workflow  $workflows
     * @return mixed
     */
    public function forceDelete(User $user, Workflow $workflows) {
        //
    }
}
