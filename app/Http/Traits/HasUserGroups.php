<?php
namespace App\Http\Traits;

use App\UserGroup;

trait HasUserGroups {
    public function userGroups() {
        return $this->belongsToMany(UserGroup::class, 'user_usergroup', 'user_id', 'usergroup_id');
    }
    
    public function isAdmin() {
        return $this->userGroups()->where('is_admin', true)->count() > 0;
    }

    public function assignUserGroup($code) {
        return $this->userGroups()->save(
            UserGroup::whereCode($code)->firstOrFail()
        );
    }

    public function assignUserGroupById($id) {
        return $this->userGroups()->save(
            UserGroup::findOrFail($id)
        );
    }

    public function assignUserGroupsByIds(array $ids) {
        return $this->userGroups()->sync($ids);
    }

    public function hasUserGroup($userGroup) {
        if (is_string($userGroup)) {
            return $this->userGroups->contains('code', $userGroup);
        }

        return !! $userGroup->intersect($this->userGroups)->count();
    }
}
