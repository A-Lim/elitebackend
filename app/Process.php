<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Casts\Json;

class Process extends Model {

    protected $fillable = ['name', 'statuses', 'seq', 'default'];
    protected $hidden = [];
    protected $appends = ['code'];
    protected $casts = ['statuses' => Json::class];

    public $timestamps = false;

    public function workflow() {
        return $this->belongsTo(Workflow::class);
    }

    public function getCodeAttribute() {
        return strtolower(str_replace(' ', '_', $this->name));
    }
}
