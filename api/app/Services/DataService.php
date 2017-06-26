<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/28
 * Time: 下午3:56
 */

namespace App\Services;


use App\Models\DtData;
use Illuminate\Support\Facades\DB;

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

            $model = new DtData();
            $model->setDeviceId($deviceId);
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

    /**
     * @param $deviceId
     * @param $timeRange
     * @param $avg
     *
     * @return array
     */
    public static function queryData($deviceId, $timeRange, $avg)
    {
        $query = DtData::queryDevice($deviceId)
            ->select('*')
            ->whereBetween('data_time', $timeRange);

        if ($avg == '5m') {
            $query->addSelect(DB::raw('concat(FROM_UNIXTIME(data_time, \'%Y-%m-%d %H:\'), floor( minute(FROM_UNIXTIME(data_time)) / 5) * 5) as avg_data_time'));
        } elseif ($avg == '1h') {
            $query->addSelect(DB::raw('FROM_UNIXTIME(data_time, \'%Y-%m-%d %H\') as avg_data_time'));
        } elseif ($avg == '1d') {
            $query->addSelect(DB::raw('FROM_UNIXTIME(data_time, \'%Y-%m-%d\') as avg_data_time'));
        }

        $query->groupBy('avg_data_time');

        $data = $query->get();
        if ($data) {
            return self::ok($data->toArray());
        }
        return self::ok([]);
    }
}