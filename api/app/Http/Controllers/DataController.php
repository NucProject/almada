<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/26
 * Time: 下午3:18
 */

namespace App\Http\Controllers;


use App\Services\DataService;
use App\Services\Errors;
use App\Services\ResultTrait;
use Illuminate\Http\Request;

class DataController extends Controller
{

    /**
     * @cat data
     * @title 数据接收接口
     * @comment 数据接收接口
     *
     * @url-param deviceId || int || 设备ID
     *
     * @form-param data || json || 数据(dataTime: 数据时间(UNIX-TIME), 其他数据项)
     *
     * @ret-val saved
     * @ret-val
     * @ret-val
     *
     * @param Request $request
     * @param int $deviceId
     * @return string
     */
    public function send(Request $request, $deviceId)
    {
        if (!ResultTrait::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Bad deviceId']);
        }

        $data = $request->input('data', '');
        if (!$data) {
            return $this->json(Errors::BadArguments, ['msg' => 'Bad data']);
        }

        $data = json_decode($data, true);

        $dataTime = $request->input('dataTime', 0);

        $saveResult = DataService::save($deviceId, $data, $dataTime);
        if ($this->isOk($saveResult)) {

            return $this->json(Errors::Ok, ['data' => $data]);
        }

        return $this->jsonFromError($saveResult);

    }
}