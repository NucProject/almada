<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/28
 * Time: 下午10:26
 */

namespace App\Services;


use App\Models\DtData;
use Illuminate\Support\Facades\DB;

class CinderellaService
{
    use ResultTrait;

    /**
     * @param $deviceId
     * @param $timeRange
     * @return array
     */
    public static function queryData($deviceId, $timeRange)
    {
        $query = DtData::queryDevice($deviceId)
            ->select('sid', DB::raw('max(flow) as maxFlow'), DB::raw('min(data_time) as timeBegin'), DB::raw('max(data_time) as timeEnd'))
            ->where('sid', '!=', '')
            ->whereBetween('data_time', $timeRange);

        $query->groupBy('sid');

        $data = $query->get();
        if ($data) {
            return self::ok($data->toArray());
        }
        return self::ok([]);

    }
}