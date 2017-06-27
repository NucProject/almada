<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/27
 * Time: 上午10:02
 */

namespace App\Services;


use App\Models\DtData;

class HpgeService
{
    /**
     * @param $deviceId
     * @param $timeRange
     * @param $sid
     * @return array
     */
    public static function queryData($deviceId, $timeRange, $sid)
    {
        $query = DtData::queryDevice($deviceId)
            ->select('*')
            ->whereBetween('data_time', $timeRange);

        if ($sid) {
            $query->where('sid', $sid);
        }

        $data = $query->get();
        if ($data) {
            return self::ok($data->toArray());
        }
        return self::ok([]);
    }
}