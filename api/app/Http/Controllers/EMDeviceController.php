<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/11/20
 * Time: 23:11
 */

namespace App\Http\Controllers;


use App\Services\EMDeviceService;
use App\Services\Errors;
use Illuminate\Http\Request;

class EMDeviceController extends Controller
{
    /**
     * @param Request $request
     * @param int $deviceId
     * @param int $n
     * @return array
     *
     * @cat data
     * @title 电磁数据统计(E参数)接口
     * @title 电磁数据统计(E参数)接口
     * @url-param deviceId || int || 设备ID
     * @url-param n || int || E参数, 例如95, 80
     * @url-param timeBegin || int || 起始时间(秒时间戳)
     * @url-param timeEnd || int || 结束时间(秒时间戳)
     * @ret-val electric || float || 1.23
     */
    public function eData(Request $request, $deviceId, $n)
    {
        if (!$n || !is_numeric($n)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $dataResult = EMDeviceService::eData($deviceId, $n, [$timeBegin, $timeEnd]);
        if (self::hasError($dataResult)) {
            return $this->jsonFromError($dataResult);
        }

        return $this->json(Errors::Ok, $dataResult['data']);
    }
}