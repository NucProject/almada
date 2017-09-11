<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/28
 * Time: 上午8:21
 */

namespace App\Http\Controllers;


use App\Services\CommandService;
use App\Services\DataService;
use App\Services\Errors;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    /**
     * @cat cmd
     * @title 发起历史数据补齐命令
     * @comment 用户向工控机发起历史数据补齐命令
     *
     * @url-param deviceId || int || 设备ID
     * @url-param date || string || 日期 ||
     * @url-param timeBegin || int || 日期 ||
     * @url-param timeEnd || int || 日期 ||
     * @ret-val timeBegin
     * @ret-val timeEnd
     *
     * @example-begin
     *
     * @example-end
     * @param Request $request
     * @param $deviceId
     * @return string
     */
    public function sendHistoryCommand(Request $request, $deviceId)
    {
        $date = $request->input('date', '');
        if ($date) {
            $timeBegin = strtotime($date);
            $timeEnd = $timeBegin + 24 * 3600;
        } else {
            $timeBegin = $request->input('timeBegin', 0);
            $timeEnd = $request->input('timeEnd', time());
        }

        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $result = CommandService::addHistoryCommand($deviceId, [$timeBegin, $timeEnd]);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        return $this->json(Errors::Ok, ['timeBegin' => $timeBegin, 'timeEnd' => $timeEnd]);
    }

    /**
     * @cat cmd
     * @title 获取历史数据补齐命令
     * @comment 工控机获取(查询)历史数据补齐命令
     *
     * @url-param deviceId || int || 设备ID
     * @url-param interval || int || 时间间隔 ||
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