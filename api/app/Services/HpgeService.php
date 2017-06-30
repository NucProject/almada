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
    use ResultTrait;
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

    public static function queryNuclide($deviceId)
    {
        $entry = DtData::queryDevice($deviceId)
            ->select('*')
            ->orderBy('data_time', 'desc')
            ->limit(1)
            ->first();

        if ($entry) {
            $array = $entry->toArray();
            $sid = $array['sid'];
            if ($sid) {
                $nuclides = DtData::queryDevice($deviceId)
                    ->select('*')
                    ->where('sid', $sid)
                    ->get()
                    ->toArray();
                return self::ok($nuclides);
            }
        }
        return self::ok([]);
    }
}