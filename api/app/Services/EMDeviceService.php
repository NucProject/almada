<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/11/20
 * Time: 23:13
 */

namespace App\Services;


use App\Models\DtData;
use Illuminate\Support\Facades\DB;

class EMDeviceService
{
    use ResultTrait;
    /**
     * @param $deviceId
     * @param $e
     * @param $timeRange
     * @return array
     */
    public static function eData($deviceId, $e, $timeRange)
    {
        $count = DtData::queryDevice($deviceId)
            ->select(DB::raw('count(1) count'))
            ->whereBetween('data_time', $timeRange)
            ->first();

        if (!$count) {
            return self::error(Errors::BadArguments);
        }

        $number = $count->toArray();
        $total = $number['count'];
        $ratio = (100 - $e) / 100;
        $index = ceil($total * $ratio);

        $elem = DtData::queryDevice($deviceId)
            ->select('electric')
            ->whereBetween('data_time', $timeRange)
            ->orderBy('electric', 'desc')
            ->offset($index)
            ->first();

        if (!$elem) {
            return self::error(Errors::BadArguments);
        }
        // echo "($ratio, $index)";
        return self::ok($elem->toArray());
    }
}