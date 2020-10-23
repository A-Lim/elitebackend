<?php
namespace App\Repositories\Permission;

interface IPermissionRepository {
     /**
     * List all permissions
     * @return array [Permissions]
     */
    public function list();
}