<?php
namespace App\Repositories\SystemSetting;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use App\SystemSetting;
use App\SystemSettingCategory;

class SystemSettingRepository implements SystemSettingRepositoryInterface {

     /**
     * {@inheritdoc}
     */
    public function list() {
        return Cache::rememberForEver(SystemSettingCategory::CACHE_KEY, function() {
            return SystemSettingCategory::with('systemsettings')->get();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findByCode(string $code) {
        return Cache::rememberForEver(SystemSetting::CACHE_KEY.'_'.$code, function() use ($code) {
            return SystemSetting::where('code', $code)->first();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodes(array $codes) {
        return SystemSetting::whereIn('code', $codes)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $data) {
        // https://github.com/laravel/ideas/issues/575
        // update multiple values based on code
        // ['code' => 'value']
        DB::transaction(function () use ($data) {
            $table = SystemSetting::getModel()->getTable();
            $cases = [];
            $codes = [];
            $params = [];

            foreach ($data as $code => $value) {
                $cases[] = "WHEN `code` = '{$code}' then ?";
                $codes[] = $code;
                if ($code === 'default_usergroups')
                    $params[] = json_encode($value);
                else
                    $params[] = $value;
            }

            $codes = "'".implode('\',\'', $codes)."'";
            $cases = implode(' ', $cases);
            $params[] = Carbon::now();
            DB::update("UPDATE `{$table}` SET `value` = CASE {$cases} END, `updated_at` = ? WHERE `code` in ({$codes})", $params);

            // cache keys are codes
            $cacheKeys = array_keys($data);
            $this->clearCaches($cacheKeys);
        });
    }

    /**
     * Clear all cache
     * 
     * @param array $codes 
     * @return void
     */
    private function clearCaches(array $codes = null) {
        Cache::forget(SystemSettingCategory::CACHE_KEY);
        if (!empty($codes)) {
            foreach ($codes as $code) {
                Cache::forget(SystemSetting::CACHE_KEY.'_'.$code);
            }
        }
    }
}