<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Schema;

use App\Permission;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\User' => 'App\Policies\UserPolicy',
        'App\UserGroup' => 'App\Policies\UserGroupPolicy',
        'App\Announcement' => 'App\Policies\AnnouncementPolicy',
        'App\SystemSetting' => 'App\Policies\SystemSettingPolicy',
        'App\Workflow' => 'App\Policies\WorkflowPolicy',
        'App\Order' => 'App\Policies\OrderPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();

        Passport::routes();
        Passport::tokensExpireIn(now()->addSeconds(env('PASSPORT_TOKEN_EXPIRATION', '3600')));
        Passport::personalAccessTokensExpireIn(now()->addSeconds(env('PASSPORT_TOKEN_EXPIRATION', '3600')));
        Passport::refreshTokensExpireIn(now()->addSeconds(env('PASSPORT_REFRESH_TOKEN_EXPIRATION', '3600')));

        if (Schema::hasTable('permissions')) {
            foreach ($this->getPermissions() as $permission) {
                Gate::define($permission->code, function($user) use ($permission) {
                    return $user->hasUserGroup($permission->userGroups);
                });
            }
        }
    }

    protected function getPermissions() {
        return Permission::with('userGroups')->get();
    }
}
