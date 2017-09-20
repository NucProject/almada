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
        $count = 0;
        foreach ($data as &$item) {

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
            $item['data_id'] = $model->data_id;
            $count += 1;
        }
        unset($item);

        return self::ok(['deviceId' => $deviceId, 'count' => $count, 'data' => $data]);
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
     * @param $order
     * @return array
     */
    public static function queryData($deviceId, $timeRange, $avg, $order='asc')
    {
        $query = DtData::queryDevice($deviceId)
            ->select('*')
            ->whereBetween('data_time', $timeRange);

        if ($avg != 'none') {
            if ($avg == '5m') {
                $query->addSelect(DB::raw('concat(FROM_UNIXTIME(data_time, \'%Y-%m-%d %H:\'), LPAD(floor(minute(FROM_UNIXTIME(data_time)) / 5) * 5, 2, \'0\') ) as avg_data_time'));
            } elseif ($avg == '1m') {
                $query->addSelect(DB::raw('concat(FROM_UNIXTIME(data_time, \'%Y-%m-%d %H:\'), LPAD(floor(minute(FROM_UNIXTIME(data_time))), 2, \'0\') ) as avg_data_time'));
            } elseif ($avg == '1h') {
                $query->addSelect(DB::raw('FROM_UNIXTIME(data_time, \'%Y-%m-%d %H:00\') as avg_data_time'));
            } elseif ($avg == '1d') {
                $query->addSelect(DB::raw('FROM_UNIXTIME(data_time, \'%Y-%m-%d 00:00\') as avg_data_time'));
            }

            $query->groupBy('avg_data_time');

        } else {
            $query->addSelect(DB::raw('FROM_UNIXTIME(data_time) as avg_data_time'));
        }

        $query->orderBy('avg_data_time', $order);
        $data = $query->get();
        if ($data) {
            return self::ok($data->toArray());
        }
        return self::ok([]);
    }

    public static function latestData($deviceId)
    {
        $query = DtData::queryDevice($deviceId)
            ->select('*')
            ->orderBy('data_time', 'desc');

        $query->limit(1);

        $data = $query->get();
        if ($data) {
            return self::ok($data->toArray());
        }
        return self::ok([]);
    }

    /**
     * @param int $deviceId
     * @param array $timeRange
     * @param array $options
     * @return array
     */
    public static function lostData($deviceId, $timeRange, array $options)
    {
        $data = DtData::queryDevice($deviceId)
            ->select('data_id', 'data_time')
            ->whereBetween('data_time', $timeRange)
            ->orderBy('data_time')
            ->get()
            ->toArray();

        $first = ['data_time' => $timeRange[0]];
        $last = ['data_time' => $timeRange[1]];

        array_unshift($data, $first);
        array_push($data, $last);

        // Array MUST has 2 items.
        $lastDataTime = $data[0]['data_time'];
        $interval = $options['interval'];

        array_shift($data);
        $lostTimePoints = [];
        foreach ($data as $item) {
            $dataTime = $item['data_time'];
            if ($dataTime - $lastDataTime != $interval) {
                for ($i = $lastDataTime + $interval; $i < $dataTime; $i += $interval) {
                    $lostTimePoints[] = $i;
                }
            }
            $lastDataTime = $dataTime;
        }

        return self::ok(['all' => 0,
                         'timePoints' => $lostTimePoints
        ]);
    }
}