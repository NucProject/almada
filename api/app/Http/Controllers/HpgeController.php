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