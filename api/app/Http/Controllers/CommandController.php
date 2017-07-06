<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/28
 * Time: 上午8:21
 */

namespace App\Http\Controllers;


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
    public function fetchHistoryCommand(Request $request, $deviceId)
    {
        $commends = [1, 2, 3, 4];
        return $this->json(Errors::Ok, ['list' => $commends]);
    }
}