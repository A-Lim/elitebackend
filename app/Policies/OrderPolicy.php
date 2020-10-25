<?php

namespace App\Policies;

use App\User;
use App\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy {
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
     * Determine whether the user can view any orders.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user) {
        return $user->can('orders.viewAny');
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param  \App\User  $user
     * @param  \App\Order  $order
     * @return mixed
     */
    public function view(User $user, Order $order) {
        return $user->can('orders.view');
    }

    /**
     * Determine whether the user can create order.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user) {
        return $user->can('orders.create');
    }

    /**
     * Determine whether the user can update the order.
     *
     * @param  \App\User  $user
     * @param  \App\Order  $order
     * @return mixed
     */
    public function update(User $user, Order $order) {
        return $user->can('orders.update');
    }

    /**
     * Determine whether the user can delete the order.
     *
     * @param  \App\User  $user
     * @param  \App\Order  $order
     * @return mixed
     */
    public function delete(User $user, Order $order) {
        return $user->can('orders.delete');
    }

    /**
     * Determine whether the user can restore the order.
     *
     * @param  \App\User  $user
     * @param  \App\Order $order
     * @return mixed
     */
    public function restore(User $user, Order $order) {
        //
    }

    /**
     * Determine whether the user can permanently delete the order.
     *
     * @param  \App\User $user
     * @param  \App\Order $order
     * @return mixed
     */
    public function forceDelete(User $user, Order $order) {
        //
    }
}
