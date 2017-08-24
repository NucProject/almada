<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/8/22
 * Time: 22:06
 */

namespace App\Services;


use App\Models\AdDataAlert;
use App\Models\AdDeviceAlertConfig;

class AlertService
{
    // Alert Config
    const AlertConfigInUse = 1;

    const AlertConfigNotInUse = 2;

    // Alert Type
    const AlertTypeThreshold    = 1;

    const AlertTypeRange        = 2;

    const AlertTypeChange       = 3;

    use ResultTrait;
    /**
     * @param $data
     * @param $deviceId
     *
     * @return array
     */
    public static function checkDataAlert($data, $deviceId)
    {
        $configs = AdDeviceAlertConfig::query()
            ->where('device_id', $deviceId)
            ->where('alert_status', self::AlertConfigInUse)
            ->get()
            ->toArray();

        if (empty($configs)) {
            return false;
        }

        foreach ($configs as $fieldConfig) {
            $fieldName = $fieldConfig['field_name'];
            $value1 = $fieldConfig['alert_value1'];
            $value2 = $fieldConfig['alert_value2'];
            $alertType = $fieldConfig['alert_type'];
            $fieldId = $fieldConfig['field_id'];

            foreach ($data as $item) {
                $dataId = $item['data_id'];
                $value = $item[$fieldName];

                //
                if ($alertType == self::AlertTypeThreshold) {
                    if ($value >= $value2) {
                        self::addDataAlert($deviceId, $dataId, $fieldId, 2);
                    } elseif ($value >= $value1) {
                        self::addDataAlert($deviceId, $dataId, $fieldId, 1);
                    }
                } elseif ($alertType == self::AlertTypeRange) {
                    if ($value > $value2 || $value < $value1) {
                        self::addDataAlert($deviceId, $dataId, $fieldId, 1);
                    }
                } elseif ($alertType == self::AlertTypeChange) {
                    if ($value == $value1) {
                        self::addDataAlert($deviceId, $dataId, $fieldId, 1);
                    }
                }
            }
        }
    }

    public static function addDataAlert($deviceId, $dataId, $fieldId, $alertLevel)
    {
        $alert = new AdDataAlert();
        $alert->device_id = $deviceId;
        $alert->data_id = $dataId;
        $alert->field_id = $fieldId;
        $alert->alert_level = $alertLevel;
        return $alert->save();
    }

    /**
     * @param $deviceId
     * @param $timeRange
     * @param array $options
     *
     * @return array
     */
    public static function getDataAlert($deviceId, $timeRange, $options=[])
    {
        $configs = AdDeviceAlertConfig::query()
            ->where('device_id', $deviceId)
            ->where('alert_status', self::AlertConfigInUse)
            ->get()
            ->toArray();

        $query = AdDataAlert::queryAll()
            ->where('ad_data_alert.device_id', $deviceId)
            ->where('ad_data_alert.status', 1)
            ->whereBetween('ad_data_alert.create_time', $timeRange);

        if (!array_key_exists('showAll', $options)) {
            $query->where('ad_data_alert.is_hide', 0);
        }

        $dataTableName = "dt_data_{$deviceId}";
        $query->leftJoin($dataTableName, "{$dataTableName}.data_id", '=', 'ad_data_alert.data_id');


        $alerts = $query->get()->toArray();

        foreach ($alerts as &$alert) {
            $fieldId = $alert['field_id'];
            foreach ($configs as $config) {
                if ($config['field_id'] == $fieldId) {
                    $alert['field_name'] = $config['field_name'];
                    // TODO: ?
                }
            }

            unset($alert['status']);
            unset($alert['create_time']);
            unset($alert['update_time']);
        }
        unset($alert);

        return self::ok($alerts);
    }
}