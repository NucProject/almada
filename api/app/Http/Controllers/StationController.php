<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/8/11
 * Time: 下午6:12
 */

namespace App\Http\Controllers;


use App\Services\Errors;
use App\Services\ResultTrait;
use App\Services\StationService;
use Illuminate\Http\Request;

class StationController extends Controller
{
    use ResultTrait;

    /**
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        // TODO: Get groupId
        $groupId = $request->input('groupId', 0);

        $data = $request->input();
        $valid = $this->validate2($data, []);
        if ($valid->fails()) {
            return $this->json(Errors::BadArguments, $valid->messages());
        }

        $stationResult = StationService::addNewStation($data, $groupId);

        if (self::isOk($stationResult)) {
            $station = $stationResult['data'];

            return $this->json(Errors::Ok, $station);
        }

        return $this->jsonFromError($stationResult);
    }

    /**
     * @param Request $request
     * @param $stationId
     */
    public function modify(Request $request, $stationId)
    {
        // TODO: Get groupId
    }

    /**
     * @param Request $request
     * @return string
     */
    public function stations(Request $request)
    {
        // TODO:
        $groupId = $request->input('groupId', 0);

        $stationsResult = StationService::getStationsInGroup($groupId);
        if (self::isOk($stationsResult)) {
            $stations = $stationsResult['data'];

            return $this->json(Errors::Ok, ['list' => $stations]);
        }

        return $this->jsonFromError($stationsResult);
    }


}