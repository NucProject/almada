<?php

namespace App\Http\Controllers;

use App\Services\Errors;
use App\Services\ConstService;
use App\Services\ResultTrait;
use App\Services\ValidateService;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller as BaseController;


class Controller extends BaseController
{
    use ResultTrait;

    public function __construct()
    {
        $this->beginTime = microtime(1);
    }

    /**
     * @param $status
     * @param $msg
     * @param $data
     * @return string
     *
     */
    private function toJson($status, $msg, $data)
    {
        $result = is_array($data) ? self::convertData($data) : $data;
        $timeCost = round(microtime(1) - $this->beginTime, 4);
        $debug = [
            'timeCost' => $timeCost
        ];
        return json_encode([
            'debug' => $debug,
            'status' => $status,
            'msg' => $msg,
            'data' => $result
        ]);
    }

    /**
     * @param $status
     * @param $data
     * @param string $msg
     * @return string
     */
    public function json($status, $data=[], $msg='')
    {
        $msg = $msg ?: Errors::getErrorMsg($status);
        return $this->toJson($status, $msg, $data);
    }

    /**
     * @param $error
     * @return string
     */
    public function jsonFromError($error)
    {
        return $this->json($error['error'], $error['data']);
    }

    /**
     * @param $request
     * @param $userType
     *
     * @return string
     */
    public function notLogin($request, $userType = ConstService::MEMBER_TYPE_MERCHANT)
    {
        $status = Errors::MERCHANT_NOT_LOGIN;
        $msg = 'Merchant Not Login';
        if ($userType == ConstService::MEMBER_TYPE_DEVELOPER)
        {
            $status = Errors::DEVELOPER_NOT_LOGIN;
            $msg = 'Developer Not Login';
        }
        if ($userType == ConstService::MEMBER_TYPE_ADMIN){
            $status = Errors::ADMIN_NOT_LOGIN;
            $msg = 'Admin Not Login';
        }

        $data = [];
        $data['userType'] = $userType;
        $data['requestUri'] = $request->getRequestUri();

        return $this->toJson($status, $msg, $data);
    }

    /**
     * @param $data
     * @return array
     */
    protected static function convertData($data)
    {
        $result = [];
        foreach ($data as $key => $value)
        {
            $key = Str::camel($key);
            if (is_string($value) || is_numeric($value) || is_bool($value)) {
                $result[$key] = $value;
            } elseif (is_array($value)) {
                $result[$key] = self::convertData($value);
            }
        }

        return $result;
    }

    /**
     * @param $input
     * @param $rules
     * @param $messages
     * @return \Illuminate\Validation\Validator
     * 提供一个不抛出异常, 并且可以轻易支持Customer Validator的校验办法,
     * 并且不需要指定Message (整个工程使用统一的Message, 除非想重新定义(覆盖)Message)
     */
    public function validate2(array $input, array $rules, array $messages=[])
    {
        $factory = $this->getValidationFactory();
        ValidateService::addExtendValidators($factory, $rules);
        $valid = $factory->make($input, $rules, $messages);
        return $valid;
    }

}
