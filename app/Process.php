<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Casts\Json;

class Process extends Model {

    protected $fillable = ['name', 'code', 'statuses', 'seq', 'pinned', 'width', 'default'];
    protected $hidden = [];
    protected $appends = [];
    protected $casts = ['statuses' => Json::class];

    public $timestamps = false;

    public function workflow() {
        return $this->belongsTo(Workflow::class);
    }
}
