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