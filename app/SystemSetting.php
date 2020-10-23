<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model {

    protected $table = 'systemsettings';
    protected $fillable = [];
    protected $hidden = [];
    protected $casts = [];

    public const CACHE_KEY = 'systemsettings';

    // verification types
    public const VTYPE_NONE = 'none';
    public const VTYPE_EMAIL = 'email';
    public const VTYPES = [
        self::VTYPE_NONE,
        self::VTYPE_EMAIL,
    ];

    public function systemSettingCategory() {
        return $this->belongsTo(SystemSettingCategory::class);
    }

    public function getValueAttribute()  {
        // system settings codes where values are stored in json
        $json_values = ['default_usergroups'];
        if ($this->attributes['value'] && in_array($this->code, $json_values))
            return json_decode($this->attributes['value']);
        else 
            return $this->attributes['value'];
    }
}
