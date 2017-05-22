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
     * @return array
     */
    public static function save($deviceId, array $data)
    {
        // 动态Model生成
        foreach ($data as $item) {

            if (!array_key_exists('dataTime', $item)) {
                // 必须在data中含有dataTime, 或者在Request中发送dataTime字段.
                return self::error(Errors::NoDataTime, []);
            }

            $model = new DtData($deviceId);
            $model->setAttributes($item, false);
            $model->status = 1;

            if (!$model->save()) {
                // 保存成功!
                return self::error(Errors::SaveFailed, []);
            }

        }

        return self::ok(['deviceId' => $deviceId]);
   }


    public static function createTable($deviceId)
    {
        $device = DeviceService::getDeviceById($deviceId);

        if (!$device) {

        }


    }
}