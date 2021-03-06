<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/8/22
 * Time: 22:06
 */

namespace App\Services;


use App\Models\AdDataAlert;
use App\Models\AdDevice;
use App\Models\AdDeviceAlertConfig;
use App\Models\AdDeviceField;
use Illuminate\Support\Str;

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
     * @param $deviceId
     * @return array
     */
    public static function getAlertConfigs($deviceId)
    {
        $configs = AdDeviceAlertConfig::query()
            ->where('device_id', $deviceId)
            ->get()
            ->toArray();

        return self::ok($configs);
    }

    /**
     * @param $deviceId
     * @param $alertConfigs
     * @return array
     */
    public static function setAlertConfigs($deviceId, $alertConfigs)
    {
        $device = AdDevice::query()->find($deviceId);
        if (!$device) {
            return self::error(Errors::DeviceNotFound);
        }

        $deviceType = $device->device_type;

        // 先全部set status -> 0
        AdDeviceAlertConfig::query()
            ->where('device_id', $deviceId)
            ->update(['status' => 0]);

        $configIdArray = [];
        $failedArray = [];
        foreach ($alertConfigs as $alertConfig) {
            $configId = false;
            if (array_key_exists('configId', $alertConfig)) {
                $configId = $alertConfig['configId'];
            }

            $config = AdDeviceAlertConfig::queryAll()
                ->where('device_id', $deviceId)
                ->where('field_id', $alertConfig['fieldId'])
                ->first();

            if (!$config) {

                $config = new AdDeviceAlertConfig();
                $config->device_id = $deviceId;
                $config->field_id = $alertConfig['fieldId'];

                $field = AdDeviceField::query()->find($config->field_id);
                if ($field && $field->device_type != $deviceType) {
                    return self::error(Errors::BadArguments, ['reason' => 'Wrong fieldId']);
                }
                $config->field_name = $field->field_name;

            }

            if ($config) {
                $config->status = 1;

                $config->alert_type = $alertConfig['alertType'];
                $config->alert_status = $alertConfig['alertStatus'];
                $config->alert_value1 = $alertConfig['alertValue1'];
                $config->alert_value2 = $alertConfig['alertValue2'];

                if ($config->save()) {
                    $configIdArray[] = $config->config_id;
                } else {
                    $failedArray[] = $alertConfig['fieldName'];
                }
            }

        }

        return self::ok(['configIdArray' => $configIdArray, 'failed' => $failedArray]);
    }

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
                    // 驼峰表达
                    $alert['field_name'] = Str::camel($config['field_name']);
                }
            }

            unset($alert['status']);
            unset($alert['create_time']);
            unset($alert['update_time']);
        }
        unset($alert);

        return self::ok($alerts);
    }

    /**
     * 策略采取最新5天的报警, 如果超过五天的报警都没有clear, 也不返回
     * @param $deviceId
     */
    public static function getLatestUnclearAlert($deviceId) {

        $now = time();
        $query = AdDataAlert::queryAll()
            ->where('ad_data_alert.device_id', $deviceId)
            ->where('ad_data_alert.status', 1)
            ->where('ad_data_alert.is_hide', 0)
            ->whereBetween('ad_data_alert.create_time', [$now - 3600*24*5, $now])
            ->orderBy('ad_data_alert.create_time', 'desc')
            ->limit(10);

        $alerts = $query->get()->toArray();
        foreach ($alerts as &$alert) {
            $fieldId = $alert['field_id'];
//            foreach ($configs as $config) {
//                if ($config['field_id'] == $fieldId) {
//                    // 驼峰表达
//                    $alert['field_name'] = Str::camel($config['field_name']);
//                }
//            }

            unset($alert['device_id']);
            unset($alert['is_hide']);
            unset($alert['status']);
            unset($alert['update_time']);
        }
        return $alerts;

    }

    public static function clearAlertsByDeviceId($deviceId) {
        // $now = time();
        $query = AdDataAlert::queryAll()
            ->where('ad_data_alert.device_id', $deviceId)
            ->where('ad_data_alert.status', 1)
            ->where('ad_data_alert.is_hide', 0);
            // ->whereBetween('ad_data_alert.create_time', [$now - 3600*24*5, $now])
            // ->orderBy('ad_data_alert.create_time', 'desc')
            // ->limit(10);
        $alerts = $query->get();
        // print_r($alerts);
        foreach ($alerts as &$alert) {
            $alert->is_hide = 1;
            $alert->save();
        }
        return 1;
    }

    public static function clearAlertsById($id) {

    }
}