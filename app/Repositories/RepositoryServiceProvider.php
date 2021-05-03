<?php
namespace App\Repositories;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider {

    public function register() {
        $this->app->bind('App\Repositories\OAuth\OAuthRepositoryInterface', 'App\Repositories\OAuth\OAuthRepository');
        $this->app->bind('App\Repositories\User\UserRepositoryInterface', 'App\Repositories\User\UserRepository');
        $this->app->bind('App\Repositories\UserGroup\IUserGroupRepository', 'App\Repositories\UserGroup\UserGroupRepository');
        $this->app->bind('App\Repositories\SystemSetting\SystemSettingRepositoryInterface', 'App\Repositories\SystemSetting\SystemSettingRepository');
        $this->app->bind('App\Repositories\Permission\IPermissionRepository', 'App\Repositories\Permission\PermissionRepository');
        $this->app->bind('App\Repositories\Workflow\IWorkflowRepository', 'App\Repositories\Workflow\WorkflowRepository');
        $this->app->bind('App\Repositories\Order\IOrderRepository', 'App\Repositories\Order\OrderRepository');
        
        $this->app->bind('App\Repositories\Dashboard\IDashboardRepository', 'App\Repositories\Dashboard\DashboardRepository');
    }
}