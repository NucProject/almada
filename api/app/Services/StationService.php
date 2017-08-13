<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/8/13
 * Time: 下午10:00
 */

namespace App\Services;


use App\Models\AdStation;

class StationService
{
    use ResultTrait;

    /**
     * @param $data
     * @param $groupId
     * @return array
     */
    public static function addNewStation($data, $groupId)
    {
        $station = new AdStation();
        $station->station_name = $data['stationName'];

        if ($station->save()) {
            
        }
    }

    public static function findStationById($stationId)
    {
        $entry = AdStation::query()->where('station_id', $stationId)->first();
        if ($entry) {
            return self::ok($entry->toArray());
        }

        return self::error(Errors::BadArguments);
    }

    /**
     * @param $groupId
     * @return array
     */
    public static function getStationsInGroup($groupId)
    {
        $stations = AdStation::query()
            ->where('group_id', $groupId)
            ->get()
            ->toArray();

        return self::ok($stations);
    }
}