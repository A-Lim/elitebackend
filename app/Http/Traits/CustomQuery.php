<?php
namespace App\Http\Traits;

trait CustomQuery {
    
    public static function buildQuery($data) {
        // returns default query
        $class = static::class;
        $query = $class::query();
        switch (env('DATATABLE_TYPE', 'DATATABLE')) {
            case 'DATATABLE':
                break;

            case 'AGGRID':
                $query = self::fromAgGrid($data);
                break;
            
            default:
                break;
        }

        return $query;
    }

    private static function fromAgGrid($data) {
        $class = static::class;
        $query = $class::query();
        // get queryable from model
        $queryable = $class::$queryable;

        foreach ($data as $key => $value) {
            if (in_array($key, $queryable)) {
                $filterData = explode(':', $value);

                if (count($filterData) < 2) {
                    // throw exception
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
}
