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

}