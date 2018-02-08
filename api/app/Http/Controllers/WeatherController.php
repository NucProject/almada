<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/29
 * Time: 下午9:13
 */

namespace App\Http\Controllers;


use App\Services\Errors;
use App\Services\ResultTrait;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    use ResultTrait;
    /**
     * @param Request $request
     * @param $deviceId
     * @return array
     *
     * @cat data
     * @title 气象雨量数据统计接口
     * @comment 在一天内最大的气象雨量
     * @url-param deviceId || int || 设备ID ||
     * @url-param timeBegin || int || 起始时间 ||
     * @url-param timeEnd || int || 结束时间 ||
     *
     * @ret-val list.0.sid
     * @ret-val list.0.maxFlow
     * @ret-val list.0.timeBegin
     * @ret-val list.0.timeEnd
     *
     * @ret-val pager.currentPage
     * @ret-val pager.totalPage
     */
    public function query(Request $request, $deviceId)
    {
        // $sid = $request->input('sid', '');

        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $result = WeatherService::queryData($deviceId, [$timeBegin, $timeEnd]);
        if (self::isOk($result)) {
            $data = $result['data'];
            return $this->json(Errors::Ok, [
                'list' => $data,
                'pager' => []
            ]);
        }
    }

    /**
     * @param Request $request
     * @param $deviceId
     * @return array
     *
     * @cat data
     * @title 气象风向风速信息
     * @comment 气象风向风速信息(主要用于绘制玫瑰图)
     * @url-param deviceId || int || 设备ID ||
     * @url-param timeBegin || int || 起始时间 ||
     * @url-param timeEnd || int || 结束时间 ||
     *
     *
     * @ret-val list.0.
     * @ret-val list.0.
     * @ret-val list.0.
     * @ret-val list.0.
     *
     */
    public function windInfo(Request $request, $deviceId)
    {
        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $degree = 22.5; // 16分图实现
        $result = WeatherService::queryWindData($deviceId, $degree, [$timeBegin, $timeEnd]);

        if (self::isOk($result)) {
            $data = $result['data'];
            return $this->json(Errors::Ok, [
                'list' => $data,
                'degree' => $degree
            ]);
        }
    }

}