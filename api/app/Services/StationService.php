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
        if (!self::isValidId($groupId)) {
            return self::error(Errors::BadArguments);
        }

        $station = new AdStation();
        $station->station_name = $data['stationName'];
        $station->station_desc = $data['stationDesc'];
        $station->station_address = $data['stationAddress'];
        $station->station_type = $data['stationType'];

        $station->group_id = $groupId;

        if (!$station->save()) {
            return self::error(Errors::SaveFailed);
        }

        return self::ok($station->toArray());
    }

    public static function updateStation($stationId, $data)
    {
        if (!self::isValidId($stationId)) {
            return self::error(Errors::BadArguments);
        }

        $station = AdStation::query()->where('station_id', $stationId)->first();

        $station->station_name = $data['stationName'];
        $station->station_desc = $data['stationDesc'];
        $station->station_address = $data['stationAddress'];
        $station->station_type = $data['stationType'];

        if (!$station->save()) {
            return self::error(Errors::SaveFailed);
        }

        return self::ok($station->toArray());
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