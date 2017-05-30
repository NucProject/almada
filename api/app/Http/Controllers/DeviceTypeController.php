<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/22
 * Time: 下午10:44
 */

namespace App\Http\Controllers;


use App\Services\DeviceTypeService;
use App\Services\Errors;
use Illuminate\Http\Request;

/**
 * Class DeviceTypeController
 * @package App\Http\Controllers
 */
class DeviceTypeController extends Controller
{
    /**
     * @cat device
     * @title 创建设备类型
     * @comment 创建设备类型
     *
     * @return string
     *
     */
    public function deviceTypes()
    {
        $result = DeviceTypeService::getAllTypes();
        if (self::isOk($result)) {
            $types = $result['data'];

            return $this->json(Errors::Ok, ['list' => $types]);
        }
        return $this->jsonFromError($result);
    }

    /**
     * @param Request $request
     * @return string
     * @cat device
     * @title 创建设备类型
     * @comment 创建设备类型
     *
     * @form-param typeName || string || 设备类型名称
     * @form-param typeDesc || string || 设备类型描述
     * @form-param deviceType
     *
     */
    public function create(Request $request)
    {
        $all = $request->input();

        $valid = $this->validate2($all, []);

        if ($valid->fails()) {
            return $this->json(Errors::BadArguments);
        }
        // TODO: 重名判断

        $result = DeviceTypeService::createDeviceType($all);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        $data = $result['data'];
        return $this->json(Errors::Ok, $data);

    }

    /**
     * @param Request $request
     * @param int $typeId
     * @return string
     *
     * @cat device
     * @title 修改设备类型字段信息
     * @comment 修改设备类型字段信息
     *
     * @form-param fields || array || 设备类型名称
     */
    public function modifyFields(Request $request, $typeId)
    {
        if (!self::isValidId($typeId)) {

        }

        $fields = $request->input('fields');

        $check = DeviceTypeService::checkFields($fields);
        if (self::hasError($check)) {
            return $this->jsonFromError($check);
        }

        var_dump($fields);
        $result = DeviceTypeService::updateFields($typeId, $fields);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        return $this->json(Errors::Ok, []);

    }

    /**
     * @param Request $request
     * @param int $typeId
     *
     * @return string
     * @cat device
     * @title 修改设备类型信息
     * @comment 修改设备类型信息基本
     *
     * @form-param typeName || string || 设备类型名称
     * @form-param typeDesc || string || 设备类型描述
     * @form-param deviceType
     *
     */
    public function modify(Request $request, $typeId)
    {
        $all = $request->input();

        $valid = $this->validate2($all, []);

        if ($valid->fails()) {
            return $this->json(Errors::BadArguments);
        }

        // TODO: 重名判断

        $result = DeviceTypeService::updateDeviceType($typeId, $all);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        $data = $result['data'];
        return $this->json(Errors::Ok, $data);
    }
}