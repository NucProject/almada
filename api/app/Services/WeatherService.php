<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/29
 * Time: 下午9:15
 */

namespace App\Services;


use App\Models\DtData;
use Illuminate\Support\Facades\DB;

class WeatherService
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
            ->select(DB::raw('max(raingauge) as maxRaingauge'), DB::raw('FROM_UNIXTIME(data_time, \'%Y-%m-%d\') as date'))
            ->whereBetween('data_time', $timeRange);

        $query->groupBy('date');

        $data = $query->get();
        if ($data) {
            return self::ok($data->toArray());
        }
        return self::ok([]);

    }

    /**
     * @param $deviceId
     * @param $degree
     * @param $timeRange
     * @return array
     * 风向风速玫瑰图 实现
     */
    public static function queryWindData($deviceId, $degree, $timeRange)
    {
        $direction = "floor((direction + 11.25) / $degree) % 16 as direction_divide";
        $query = DtData::queryDevice($deviceId)
            ->select(DB::raw('count(wind_speed) as wind_freq'), DB::raw($direction))
            ->whereBetween('data_time', $timeRange);

        $query->groupBy('direction_divide');

        $data = $query->get();
        if ($data) {
            return self::ok($data->toArray());
        }

        return self::ok([]);
    }
}