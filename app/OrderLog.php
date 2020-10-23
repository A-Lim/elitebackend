<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model {

    protected $fillable = ['workflow_id', 'order_id', 'process_id', 'from_status', 'to_status', 'created_by', 'created_at'];
    protected $hidden = [];
    public $timestamps = false;

    public function workflow() {
        return $this->belongsTo(Workflow::class);
    }

    public function process() {
        return $this->belongsTo(Process::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
