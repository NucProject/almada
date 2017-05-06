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
     * @form-param history || int || 是否是历史数据(默认值为0, 表示实时数据; 1表示历史数据)
     * @form-param data || array || Form表单Array, data[]={dataTime=UNIX-TIME, 其他字段值}
     *
     * @ret-val saved.deviceId || int || 保存成功的信息 || 1
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

        $data = $request->input('data', []);
        if (!$data || !is_array($data)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Bad data']);
        }

        $saveResult = DataService::save($deviceId, $data);
        if ($this->isOk($saveResult)) {

            $saved = $saveResult['data'];
            return $this->json(Errors::Ok, ['saved' => $saved]);
        }

        return $this->jsonFromError($saveResult);

    }
}