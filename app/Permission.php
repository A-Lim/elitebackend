<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

use App\UserGroup;

class Permission extends Model {

    protected $fillable = ['code', 'label', 'module', 'description'];
    protected $hidden = ['pivot'];
    protected $casts = [];

    public function userGroups() {
        return $this->belongsToMany(UserGroup::class, 'permission_usergroup', 'permission_id', 'usergroup_id');
    }
}
