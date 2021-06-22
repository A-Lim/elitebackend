<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Workflow;
use App\OrderFile;

class Order extends Model {

    protected $guarded = [];
    protected $hidden = [];
    protected $casts = [
        'delivery_date' => 'datetime:d/m/Y',
        'completed_at' => 'datetime:d/m/Y',
    ];

    const STATUS_INPROGRESS = 'in progress';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_INPROGRESS,
        self::STATUS_COMPLETED,
    ];

    public function scopeFromTable($query, $tableName) {
        $this->setTable($tableName);
        $query->from($tableName.' as orders');
    }

    public function scopeAgGridQuery($query, $data) {
        unset($data['limit']);
        unset($data['page']);

        foreach ($data as $key => $value) {
            $filterData = explode(':', $value);

            if (count($filterData) < 2) {
            }

            $filterType = strtolower($filterData[0]);
            $filterVal = $filterData[1];

            switch($filterType) {
                case 'contains':
                    $query->where($key, 'LIKE', '%'.$filterVal.'%');
                    break;
                
                case 'equals':
                    if (in_array($key, ['created_at', 'updated_at']))
                        $query->whereDate($key, $filterVal);
                    else
                        $query->where($key, $filterVal);
                    break;
                
                default:
                    // throw errror
                    break;
            }
        }

        if (array_key_exists('sort', $data)) {
            $sortData = explode(';', $data['sort']);
            foreach($sortData as $sortDetail) {
                $sortData = explode(':', $sortDetail);
                
                if (count($sortData) < 2) {
                    // throw exception
                }
                $sortCol = $sortData[1];
                $sortType = $sortData[0];

                $query->orderBy($sortCol, $sortType);
            }
        }
        
        return $query;
    }
    
    public function files() {
        return $this->hasMany(OrderFile::class, 'order_id');
    }
}
