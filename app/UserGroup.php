<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Http\Traits\CustomQuery;

class UserGroup extends Model {
    use SoftDeletes, CustomQuery;

    protected $table = 'usergroups';
    protected $fillable = ['name', 'code', 'status', 'is_admin', 'deleted_at', 'created_by', 'updated_by'];
    protected $hidden = ['deleted_at', 'pivot', 'created_at', 'updated_at'];
    protected $casts = ['is_admin' => 'boolean'];

    // list of properties queryable for datatable
    public static $queryable = ['name', 'code', 'status', 'is_admin', 'deleted_at', 'created_by', 'updated_by'];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE
    ];

    /**
     * Model events
     *
     * @return void
     */
    public static function boot() {
        parent::boot();

        self::creating(function($model) {
            // if status is not provided
            // set default to unverified
            if (empty($model->status)) {
                $model->status = self::STATUS_ACTIVE;
            }
        });
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_usergroup', 'usergroup_id', 'user_id'); 
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'permission_usergroup', 'usergroup_id', 'permission_id'); 
    }

    public function givePermissions(array $ids) {
        $this->permissions()->sync($ids);
    }
}
