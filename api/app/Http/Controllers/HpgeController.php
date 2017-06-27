<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/27
 * Time: 上午9:47
 */

namespace App\Http\Controllers;


use App\Services\Errors;
use App\Services\HpgeService;
use Illuminate\Http\Request;

class HpgeController extends Controller
{
    /**
     * @param Request $request
     * @param $deviceId
     * @return array
     *
     * @cat data
     * @title HPGE数据查询接口
     * @comment HPGE数据查询接口
     * @url-param deviceId || int || 设备ID ||
     * @url-param sid || string || SID ||
     * @ret-val list.0.dataTime
     * @ret-val list.0.someFieldValue
     *
     * @ret-val pager.currentPage
     * @ret-val pager.totalPage
     */
    public function query(Request $request, $deviceId)
    {
        $sid = $request->input('sid', '');

        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $result = HpgeService::queryData($deviceId, [$timeBegin, $timeEnd], $sid);
        if (self::isOk($result)) {
            $data = $result['data'];
            return $this->json(Errors::Ok, [
                'list' => $data,
                'pager' => []
            ]);
        }
    }
}