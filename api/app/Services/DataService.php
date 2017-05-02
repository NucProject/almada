<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/28
 * Time: 下午3:56
 */

namespace App\Services;


use App\Models\DtData;

class DataService
{
    use ResultTrait;
    /**
     * @param int $deviceId
     * @param array $data
     * @param int $dataTime
     * @return array
     */
    public static function save($deviceId, $data, $dataTime=0)
    {
        // 动态Model生成
        $model = new DtData($deviceId);

        if (!array_key_exists('dataTime', $data) && !$dataTime) {
            // 必须在data中含有dataTime, 或者在Request中发送dataTime字段.
            return self::error(Errors::NoDataTime, []);
        }

        if (!$dataTime) {
            $dataTime = $data['dataTime'];
        }
        unset($data['dataTime']);

        $model->setAttributes($data, false);

        $model->data_time = $dataTime;
        $model->status = 1;

        if ($model->save()) {
            // 保存成功!
            return self::ok(['deviceId' => $deviceId]);
        }

        return self::error(Errors::SaveFailed, []);
    }


    public static function createTable($deviceId)
    {
        $device = DeviceService::getDeviceById($deviceId);

        if (!$device) {

        }


    }
}