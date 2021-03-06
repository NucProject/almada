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
     * @ret-val list.0.typeId
     * @ret-val list.0.dependTypeId
     * @ret-val list.0.typeName
     * @ret-val list.0.typeTitle
     * @ret-val list.0.typeDesc
     * @ret-val list.0.typeCreator
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
     * @form-param isMovable || int || 是否是移动设备
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
     * @url-param typeId || int || TypeID
     * @form-param fields || array || 设备类型名称(参加返回值字段)
     *
     * @ret-val fields.0.fieldId
     * @ret-val fields.0.fieldName
     * @ret-val fields.0.fieldTitle
     *
     * @case @form
     */
    public function modifyFields(Request $request, $typeId)
    {
        if (!self::isValidId($typeId)) {
            return $this->json(Errors::BadArguments);
        }

        $fields = $request->input('fields');

        $check = DeviceTypeService::checkFields($fields);
        if (self::hasError($check)) {
            return $this->jsonFromError($check);
        }

        $result = DeviceTypeService::updateFields($typeId, $fields);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        return $this->json(Errors::Ok, [
            'fields' => $result['data']
        ]);
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
     * @form-param isMovable || int || 是否是移动设备
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

    /**
     * @param Request $request
     * @param $typeId
     * @return string
     *
     * @url-param typeId || int || Type ID
     *
     * @ret-val list.0.fieldId
     * @ret-val list.0.fieldType
     * @ret-val list.0.fieldName
     * @ret-val list.0.fieldConfig
     * @ret-val list.0.fieldTitle
     * @ret-val list.0.fieldDesc
     * @ret-val list.0.fieldUnit
     * @ret-val list.0.displayFlag
     * @ret-val list.0.alertFlag
     */
    public function getFields(Request $request, $typeId)
    {
        $fieldsResult = DeviceTypeService::getFieldsByTypeId($typeId);

        if (self::hasError($fieldsResult)) {
            return $this->jsonFromError($fieldsResult);
        }

        $data = $fieldsResult['data'];
        return $this->json(Errors::Ok, ['list' => $data]);
    }
}