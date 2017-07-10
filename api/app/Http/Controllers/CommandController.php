<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/28
 * Time: 上午8:21
 */

namespace App\Http\Controllers;


use App\Models\AdCommand;
use App\Services\CommandService;
use App\Services\DataService;
use App\Services\Errors;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    /**
     * @cat cmd
     * @title 查询设备某时间段内数据完整性
     * @comment 查询设备某时间段内数据完整性
     *
     * @param Request $request
     * @param int $deviceId
     * @return string
     *
     *
     */
    public function integrityQuery(Request $request, $deviceId)
    {
        $timeBegin = $request->input('timeBegin', 0);

        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $commends = [];
        $commends[] = ['type' => 'history', 'timeBegin' => 1, 'timeEnd' => 10];
        return $this->json(Errors::Ok, ['list' => $commends]);
    }

    /**
     * @cat cmd
     * @title 发起历史数据补齐命令
     * @comment 用户向工控机发起历史数据补齐命令
     *
     * @param Request $request
     * @param $deviceId
     * @return string
     */
    public function sendHistoryDataCommand(Request $request, $deviceId)
    {
        $timeBegin = $request->input('timeBegin', 0);

        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $result = CommandService::addHistoryCommand($deviceId, [$timeBegin, $timeEnd]);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

    }

    /**
     * @cat cmd
     * @title 获取历史数据补齐命令
     * @comment 工控机获取(查询)历史数据补齐命令
     *
     * @param Request $request
     * @param $deviceId
     * @return string
     */
    public function fetchHistoryCommand(Request $request, $deviceId)
    {
        $result = CommandService::fetchDeviceHistoryCommand($deviceId);
        if (self::isOk($result)) {

            $timeRange = $result['data'];

            $interval = $request->input('interval', 30);
            $lost = DataService::lostData($deviceId, $timeRange, ['interval' => $interval]);
            if (self::isOk($lost)) {
                return $this->json(Errors::Ok, $lost['data']);
            }
        }

        return $this->jsonFromError($result);
    }
}