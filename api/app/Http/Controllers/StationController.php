<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/8/11
 * Time: 下午6:12
 */

namespace App\Http\Controllers;


use App\Services\Errors;
use App\Services\GroupService;
use App\Services\ResultTrait;
use App\Services\StationService;
use App\Services\UserService;
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
     *
     * @example-begin cURL
     * curl -d"stationName=station&stationDesc=XYZ&stationAddress=someAddress&stationType=1" http://127.0.0.1:1024/d/station?groupId=1
     * @example-end
     *
     */
    public function create(Request $request)
    {
        // TODO: Get groupId
        $groupId = $request->input('groupId', 0);

        $data = $request->input();
        $valid = $this->validate2($data, [
            'stationName' => 'required|string',
            'stationDesc' => 'required|string',
            'stationAddress' => 'required|string',
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
     * @return string
     *
     * @cat station
     * @title 编辑自动站信息
     * @comment 编辑自动站信息
     *
     * @url-param $stationId || int || station id
     * @form-param stationName || string || 自动站名称
     * @form-param stationDesc || string || 自动站描述
     * @form-param stationAddress || string || 自动站地址
     * @form-param stationType || int || 自动站类型 (1, 固定自动站; 2, 移动自动站; 3, 虚拟自动站)
     *
     * @ret-val stationName
     * @ret-val stationDesc
     * @ret-val stationAddress
     * @ret-val stationType
     *
     * @example-begin cURL
     * curl -d"stationName=new-station-name&stationDesc=desc&stationAddress=&stationType=1"  http://127.0.0.1:1024/d/station/4?groupId=1
     * @example-end
     */
    public function modify(Request $request, $stationId)
    {
        // TODO: Get groupId
        $groupId = $request->input('groupId', 0);

        $data = $request->input();
        $valid = $this->validate2($data, [
            'stationName' => 'required|string',
            'stationDesc' => 'required|string',
            'stationAddress' => 'required|string',
            'stationType' => 'required|int'
        ]);

        if ($valid->fails()) {
            return $this->json(Errors::BadArguments, $valid->messages());
        }

        $stationResult = StationService::updateStation($stationId, $data);

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
     * @title 删除自动站
     * @comment 删除自动站(未实现)
     *
     */
    public function remove(Request $request, $stationId)
    {
        // TODO: 删除 $stationId
    }

    /**
     * @param Request $request
     * @return string
     * @cat station
     * @title 自动站列表
     * @comment 列出当前用户组下所有的自动站
     *
     * @ret-val list.0.stationId
     * @ret-val list.0.stationName
     * @ret-val list.0.stationDesc
     * @ret-val list.0.stationAddress
     * @ret-val list.0.stationType
     * @ret-val list.0.stationSn || string || Station SN(目前还没有支持) || S00001
     */
    public function stations(Request $request)
    {
        $userId = $request->user()->getUid();

        $userResult = UserService::getUserById($userId);
        if (self::hasError($userResult)) {
            return $this->jsonFromError($userResult);
        }

        $user = $userResult['data'];

        $stationsResult = StationService::getStationsInGroup($user->group_id);
        if (self::isOk($stationsResult)) {
            $stations = $stationsResult['data'];

            return $this->json(Errors::Ok, ['list' => $stations]);
        }

        return $this->jsonFromError($stationsResult);
    }


}