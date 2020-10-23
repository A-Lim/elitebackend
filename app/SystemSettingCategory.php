<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemSettingCategory extends Model {

    protected $table = 'systemsettingcategories';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];
    
    public $timestamps = false;

    public const CACHE_KEY = 'systemsettingcategories';

    public function systemSettings() {
        return $this->hasMany(SystemSetting::class, 'systemsettingcategory_id');
    }
}
