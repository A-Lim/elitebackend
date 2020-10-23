<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionModule extends Model {

    protected $fillable = ['code', 'name', 'description', 'is_active'];
    protected $hidden = [];
    protected $casts = [];

    public function permissions() {
        return $this->hasMany(Permission::class);
    }
}
