<?php
namespace App;

use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderFile extends Model {

    protected $fillable = ['workflow_id', 'order_id', 'name', 'path', 'type', 'uploaded_by', 'uploaded_at'];
    protected $hidden = [];
    public $timestamps = false;

    public function workflow() {
        return $this->belongsTo(Workflow::class);
    }

    public function getPathAttribute($value) {
        return url(Storage::url($value));
    }
}
