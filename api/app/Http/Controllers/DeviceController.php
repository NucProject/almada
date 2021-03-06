<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/26
 * Time: 下午3:19
 */

namespace App\Http\Controllers;


use App\Models\AdDevice;
use App\Models\DtData;
use App\Services\DeviceService;
use App\Services\DeviceTypeService;
use App\Services\Errors;
use App\Services\RedisService;
use App\Services\ResultTrait;
use App\Services\UserService;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * @cat device
     * @title 设备列表
     * @comment 当前用户组下(自动站下,如果stationId有值)设备列表
     *
     * @url-param groupId || int || 分组ID ||
     * @url-param stationId || int || Station ID
     * @url-param deviceOnly || int || 是否只含有设备信息 ||
     *
     * @ret-val list.0.deviceId
     * @ret-val list.0.deviceName
     * @ret-val list.0.createTime
     * @ret-val list.0.data.dataId
     * @ret-val list.0.data.dataTime
     *
     * @ret-val list.0.fields.0.fieldName
     * @ret-val list.0.fields.0.fieldTitle
     * @ret-val list.0.fields.0.fieldDesc
     * @ret-val list.0.fields.0.displayFlag
     *
     *
     * @param Request $request
     * @return string
     */
    public function devices(Request $request)
    {
        $stationId = $request->input('stationId', 0);
        $groupId = $request->input('groupId', 0);

        // TODO:

        $devicesResult = DeviceService::getDevices($groupId, $stationId);
        if (self::hasError($devicesResult)) {
            return $this->jsonFromError($devicesResult);
        }

        $devices = $devicesResult['data'];

        $deviceOnly = $request->input('deviceOnly', 0);
        if (!$deviceOnly) {
            foreach ($devices as &$device) {
                $deviceId = $device['device_id'];
                $typeId = $device['type_id'];

                $titleResult =  DeviceTypeService::getTypeTitle($typeId);
                if (self::isOk($titleResult)) {
                    $device['type_title'] = $titleResult['data'];
                } else {
                    $device['type_title'] = '?';
                }

                $typeResult = DeviceTypeService::getFieldsByTypeId($typeId);

                if (self::isOk($typeResult)) {
                    $fields = $typeResult['data'];

                    foreach ($fields as &$field) {
                        unset($field['type_id']);
                        unset($field['status']);
                        unset($field['create_time']);
                        unset($field['update_time']);
                    }
                    unset($field);
                    $device['fields'] = $fields;
                }

                // TODO: Optimize: order by cause slow query. use redis!
                $latestData = RedisService::getLatestData($deviceId);
                if (!$latestData) {
                    $dataEntry = DtData::queryDevice($deviceId)
                        ->select('*')
                        ->orderBy('data_time', 'desc')
                        ->first();
                    if ($dataEntry) {
                        $data = $dataEntry->toArray();
                        unset($data['status']);
                        unset($data['create_time']);
                        unset($data['update_time']);
                        $device['data'] = $data;
                    }
                } else {
                    $device['data'] = $latestData;
                }


                unset($device['status']);
                unset($device['group_id']);
            }

            unset($device);
        }

        $data = [
            'list' => $devices
        ];
        return $this->json(Errors::Ok, $data);
    }

    /**
     * @cat device
     * @title 全部设备列表
     * @comment 全部设备列表
     *
     * @url-param groupId || int || 分组ID ||
     * @url-param typeId || int || Type ID (支持TypeId筛选)
     *
     * @ret-val list.0.deviceId
     * @ret-val list.0.deviceName
     * @ret-val list.0.createTime
     * @ret-val list.0.data.dataId
     * @ret-val list.0.data.dataTime
     *
     * @ret-val list.0.fields.0.fieldName
     * @ret-val list.0.fields.0.fieldTitle
     * @ret-val list.0.fields.0.fieldDesc
     * @ret-val list.0.fields.0.displayFlag
     *
     *
     * @param Request $request
     * @return string
     */
    public function allDevices(Request $request)
    {
        $typeId = $request->input('typeId', 0);

        $devicesResult = DeviceService::getAllDevices(['typeId' => $typeId]);
        if (self::hasError($devicesResult)) {
            return $this->jsonFromError($devicesResult);
        }

        $devices = $devicesResult['data'];

        $data = [
            'list' => $devices
        ];
        return $this->json(Errors::Ok, $data);
    }

    /**
     * @cat device
     * @title 创建设备
     * @comment 创建设备
     *
     * @form-param groupId || int || 分组ID
     * @form-param stationId || int || Station ID
     * @form-param typeId || int || 设备类型ID
     * @form-param deviceName || string || 设备名称 ||
     * @form-param deviceSn || string || 设备SN ||
     *
     * @ret-val device.deviceId
     * @ret-val device.deviceName
     * @ret-val device.typeId
     * @ret-val device.groupId
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        $stationId = $request->input('stationId', 0);
        if (!ResultTrait::isValidId($stationId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'The Station-Id  is required']);
        }

        // 设备必有其类型!
        $typeId = $request->input('typeId', 0);
        if (!self::isValidId($typeId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Type id is required']);
        }

        $data = $request->input();
        $result = DeviceService::createDevice($stationId, $typeId, 0, $data);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        $deviceId = $result['data']['device_id'];

        $dependResult = DeviceTypeService::getDeviceTypeIdArrayByDependTypeId($typeId);
        if (self::isOk($dependResult)) {
            // 附属类型
            $subTypeList = $dependResult['data'];
            foreach ($subTypeList as $subType) {
                $subTypeResult = DeviceService::createDevice($stationId, $subType['type_id'], $deviceId, $data);
                if (self::hasError($subTypeResult)) {
                    return $this->jsonFromError($subTypeResult);
                }
            }
        }

        return $this->json(Errors::Ok, $result['data']);
    }

    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat device
     * @title 设备详情
     * @comment 根据设备ID获得设备详细信息
     *
     * @url-param deviceId || int || 设备ID
     * @url-param details || int || 是否返回详细信息 ||
     *
     * @ret-val deviceName
     * @ret-val deviceDesc
     * @ret-val deviceSn
     * @ret-val stationId
     *
     */
    public function deviceInfo(Request $request, $deviceId)
    {
        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Invalid Device id']);
        }

        $deviceResult = DeviceService::getDeviceById($deviceId);
        if (self::isOk($deviceResult)) {
            $device = $deviceResult['data'];
            return $this->json(Errors::Ok, $device);
        }

        return $this->jsonFromError($deviceResult);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function typedDevices(Request $request)
    {
        $userId = $request->user()->getUid();

        $userResult = UserService::getUserById($userId);
        if (self::hasError($userResult)) {
            return $this->jsonFromError($userResult);
        }

        $user = $userResult['data'];
        $groupId = $user['group_id'];

        $devicesResult = DeviceService::getDevices($groupId);
        if (self::hasError($devicesResult)) {
            return $this->jsonFromError($devicesResult);
        }

        $devices = $devicesResult['data'];
        $typeIdArray = array_unique(array_map(function($i) { return $i['type_id'];}, $devices));

        $typesResult = DeviceTypeService::getAllTypes($typeIdArray);
        if (self::hasError($typesResult)) {
            return $this->jsonFromError($typesResult);
        }

        $types = $typesResult['data'];

        foreach ($types as &$type) {
            $typeId = $type['type_id'];
            $type['devices'] = array_values(
                array_filter($devices, function($i) use ($typeId) { return $i['type_id'] == $typeId; }));
        }
        unset($type);

        return $this->json(Errors::Ok, ['list' => $types]);
    }
}