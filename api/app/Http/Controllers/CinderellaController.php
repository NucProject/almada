<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/28
 * Time: 下午10:25
 */

namespace App\Http\Controllers;




use App\Services\CinderellaService;
use App\Services\Errors;
use App\Services\ResultTrait;
use Illuminate\Http\Request;

class CinderellaController extends Controller
{
    use ResultTrait;
    /**
     * @param Request $request
     * @param $deviceId
     * @return array
     *
     * @cat data
     * @title Cinderella数据统计接口
     * @comment 在一个时间段内所有的SID已经对应最大的流量(Flow)
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

        $result = CinderellaService::queryData($deviceId, [$timeBegin, $timeEnd]);
        if (self::isOk($result)) {
            $data = $result['data'];
            return $this->json(Errors::Ok, [
                'list' => $data,
                'pager' => []
            ]);
        }
    }


}