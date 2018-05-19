<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/28
 * Time: 下午3:56
 */

namespace App\Services;


use App\Models\AdDevice;
use App\Models\AdDeviceField;
use App\Models\AdDeviceType;
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
        foreach ($data as $key => &$item) {

            if (!array_key_exists('dataTime', $item)) {
                file_put_contents('/var/www/almada/api/storage/logs/post.log', $key . json_encode($item) ."\n", FILE_APPEND);
                return self::error(Errors::NoDataTime, []);
            }

            $model = new DtData();
            $model->setDeviceId($deviceId);
            $model->setAttributes($item, false);
            $model->status = 1;

            if (!$model->save()) {
                // 保存!成功!
                return self::error(Errors::SaveFailed, []);
            }
            $item['data_id'] = $model->data_id;
            $count += 1;
            RedisService::setLatestData($deviceId, $item);
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
     * TODO: avg
     * @param $deviceId
     * @param $timeRange
     * @param $avg
     * @param $fieldsSet
     * @param $order
     * @return array
     */
    public static function queryData($deviceId, $timeRange, $avg, $fieldsSet, $order='asc')
    {
        $fieldsSetArray = explode(',', trim($fieldsSet, ', '));
        $fieldsSetArray = array_filter($fieldsSetArray, function($i) { return $i; });

        $device = AdDevice::query()->find($deviceId);
        if (!$device) {
            return self::error(Errors::DeviceNotFound);
        }

        $fields = AdDeviceField::query()
            ->where('type_id', $device->type_id)->get()->toArray();

        if (empty($fields)) {
            return self::error(Errors::DeviceNotFound);
        }

        $query = DtData::queryDevice($deviceId)
            ->select('data_time');


        if ($device->movable) {
            $query->addSelect('lat')->addSelect('lng');
        }

        if ($avg != 'none') {

            foreach ($fields as $field) {
                $fieldName = $field['field_name'];

                if (!empty($fieldsSetArray) && !in_array(camel_case($fieldName), $fieldsSetArray)) {
                    continue;
                }

                $fieldConfig = $field['field_config'];

                if (strstr($fieldConfig, 'max')) {
                    $query->addSelect(DB::raw("round(max($fieldName), 2) as {$fieldName}"));
                    continue;
                } elseif (strstr($fieldConfig, 'no-sel')) {
                    continue;
                }
                $query->addSelect(DB::raw("round(avg($fieldName), 2) as {$fieldName}"));
            }

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
            $query->addSelect('data_id');
            foreach ($fields as $field) {
                $fieldName = $field['field_name'];
                if (!empty($fieldsSetArray) && !in_array(camel_case($fieldName), $fieldsSetArray)) {
                    continue;
                }

                $query->addSelect($fieldName);
            }

            $query->addSelect(DB::raw('FROM_UNIXTIME(data_time) as avg_data_time'));
        }

        $templateQuery = clone $query;

        $query->whereBetween('data_time', $timeRange);

        $query->orderBy('avg_data_time', $order);
        $data = $query->get();
        if ($data) {
            $data = $data->toArray();
            if (!empty($data)) {
                return self::ok($data);
            }
        }

        $first = $templateQuery->first();

        if ($first) {
            return self::error(Errors::ResourceNotFound, ['template' => $first->toArray()]);
        }
        return self::error(Errors::BadArguments);
    }

    public static function queryDataFirst($deviceId)
    {

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

    /**
     * @param $deviceId
     * @param $timeRange
     * @param array $options
     * @return array
     */
    public static function getDeviceDataRatio($deviceId, $timeRange, array $options)
    {
        $device = AdDevice::query()->find($deviceId);
        if (!$device) {
            return self::error(Errors::DeviceNotFound);
        }

        $deviceType = AdDeviceType::query()->where('type_id', $device->type_id)->first();
        if (!$deviceType) {
            return self::error(Errors::DeviceNotFound);
        }

        $interval = $deviceType->interval;
        $count = 24 * 3600 / $interval;

        $query = DtData::queryDevice($deviceId)
            ->select(DB::raw('count(1) count'))
            ->addSelect(DB::raw('FROM_UNIXTIME(data_time, \'%Y-%m-%d\') as date'))
            ->whereBetween('data_time', $timeRange);

        $query->groupBy('date');
        $data = $query->get()->toArray();

        $totalDaily = isset($options['totalDaily']) ? $options['totalDaily'] : $count;
        foreach ($data as &$item) {

            $item['ratio'] = round($item['count'] * 100 / $totalDaily, 2);
        }
        unset($item);
        return self::ok($data);
    }

    /**
     * @param $ratios
     * @param [$timeBegin, $timeEnd]
     * @return array
     */
    public static function paddingRatios($ratios, $timeRange) {

        $b = $timeRange[0];
        $e = $timeRange[1];

        $array = [];
        for ($i = $b; $i <= $e; $i += 3600 * 24) {

            $time = 0;
            if (count($ratios) > 0) {
                $first = $ratios[0];
                $time = $first['date'];
            }

            if ($time != $i) {
                array_push($array, ['date' => date('Y-m-d', $i), 'count' => 0, 'ratio' => 0]);
            } else {
                array_push($array, $first);
                array_shift($array);
            }

        }
        return $array;
    }
}