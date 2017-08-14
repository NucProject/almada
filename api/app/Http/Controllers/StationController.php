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
     *
     * @cat station
     * @title 创建自动站
     * @comment 在当前用户组下创建一个自动站
     *
     * @form-param stationName || string || 自动站名称
     * @form-param stationDesc || string || 自动站描述
     * @form-param stationAddress || string || 自动站地址
     * @form-param stationType || int || 自动站类型 (1, 固定自动站; 2, 移动自动站; 3, 虚拟自动站)
     *
     * @ret-val stationName
     * @ret-val stationDesc
     * @ret-val stationAddress
     * @ret-val stationType
     */
    public function create(Request $request)
    {
        // TODO: Get groupId
        $groupId = $request->input('groupId', 0);

        $data = $request->input();
        $valid = $this->validate2($data, [
            'stationName' => 'required|string',
            'stationDesc' => 'string',
            'stationAddress' => 'string',
            'stationType' => 'required|int'
        ]);

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
     *
     * @cat station
     * @title 编辑自动站信息
     * @comment 编辑自动站信息
     */
    public function modify(Request $request, $stationId)
    {
        // TODO: Get groupId
    }

    /**
     * @param Request $request
     * @return string
     * @cat station
     * @title 自动站列表
     * @comment 列出当前用户组下所有的自动站
     *
     * @ret-val list.0.stationName
     * @ret-val list.0.stationDesc
     * @ret-val list.0.stationAddress
     * @ret-val list.0.stationType
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