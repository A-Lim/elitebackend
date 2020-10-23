<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Http\Traits\CustomQuery;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model {
    use CustomQuery, SoftDeletes;

    protected $fillable = ['name', 'status', 'created_by', 'updated_by'];
    protected $hidden = [];

    public static $queryable = ['name', 'status', 'created_by', 'created_at'];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE
    ];

    public function processes() {
        return $this->hasMany(Process::class, 'workflow_id');
    }
}
