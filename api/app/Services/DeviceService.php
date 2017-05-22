<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/29
 * Time: 下午7:27
 */

namespace App\Services;


use App\Models\AdDevice;

class DeviceService
{
    /**
     * @param $deviceId
     * @return \App\Models\Base\AdDeviceBase;
     */
    public static function getDeviceById($deviceId)
    {
        $device = AdDevice::query()->find($deviceId);
        return $device;
    }

    /**
     * @param $groupId
     * @param $deviceTypeId
     *
     * @return array
     */
    public static function createDevice($groupId, $deviceTypeId)
    {
        // TODO: deviceType find

        $device = new AdDevice();
        $device->group_id = $groupId;

        if (!$device->save()) {

        }

        // TODO: 创建数据表
    }
}