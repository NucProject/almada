<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/8/22
 * Time: 22:24
 */

namespace App\Http\Controllers;


use App\Services\AlertService;
use App\Services\DeviceService;
use App\Services\DeviceTypeService;
use App\Services\Errors;
use App\Services\ResultTrait;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    use ResultTrait;

    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat device
     * @title 获取设备的报警设置
     * @comment 根据设备ID获取设备的报警设置
     * @url-param deviceId || int || 设备ID
     *
     * @ret-val list.0.configId
     * @ret-val list.0.fieldId
     * @ret-val list.0.fieldName
     * @ret-val list.0.alertType
     * @ret-val list.0.alertValue1
     * @ret-val list.0.alertValue2
     * @ret-val list.0.alertStatus
     * @ret-val list.0.fieldTitle
     *
     */
    public function alertConfigs(Request $request, $deviceId)
    {
        $deviceResult = DeviceService::getDeviceById($deviceId);
        if (self::hasError($deviceResult)) {
            return $this->jsonFromError($deviceResult);
        }

        $device = $deviceResult['data'];

        $fieldResult = DeviceTypeService::getFieldsByTypeId($device['type_id']);
        if (self::hasError($fieldResult)) {
            return $this->jsonFromError($fieldResult);
        }
        $fields = $fieldResult['data'];

        $configResult = AlertService::getAlertConfigs($deviceId);
        if (self::hasError($configResult)) {
            return $this->jsonFromError($configResult);
        }

        $configs = $configResult['data'];

        $data = [];
        foreach ($fields as $field) {
            if ($field['alert_flag'] == 0) {
                continue;
            }

            $fieldName = $field['field_name'];

            $alert = self::getFieldAlertInfoByName($fieldName, $configs);

            if ($alert) {
                unset($alert['device_id']);
                unset($alert['create_time']);
                unset($alert['update_time']);
                unset($alert['status']);
            } else {
                $alert['config_id'] = 0;
                $alert['field_id'] = $field['field_id'];
                $alert['field_name'] = $fieldName;
                $alert['alert_status'] = 0;
                $alert['alert_type'] = 0;
                $alert['alert_value1'] = 0;
                $alert['alert_value2'] = 0;
            }

            $alert['field_title'] = $field['field_title'];

            $data[] = $alert;
        }
        return $this->json(Errors::Ok, [
            'list' => $data,
            // 'fields' => $fieldResult['data']
        ]);
    }

    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat device
     * @title 更改设备的报警设置
     * @comment 更改设备的报警设置 alertConfigs是表单参数, 支持多个字段的报警设置
     * @url-param deviceId || int || 设备ID
     * @form-param alertConfigs || form || 表单数组, 包含configId(没有则填0), fieldId, fieldName, alertStatus, alertType, alertValue1, alertValue2
     *
     * @example-begin
     * curl -d "alertConfigs[0][configId]=1&alertConfigs[0][fieldId]=5&alertConfigs[0][fieldName]=electric&alertConfigs[0][alertStatus]=1&alertConfigs[0][alertType]=1&alertConfigs[0][alertValue1]=10&alertConfigs[0][alertValue2]=15" http://127.0.0.1:1024/d/device/1/alertConfigs
     * @example-end
     */
    public function setAlertConfigs(Request $request, $deviceId)
    {
        $alertConfigs = $request->input('alertConfigs');

        $alertConfigs = AlertService::setAlertConfigs($deviceId, $alertConfigs);
        if (self::hasError($alertConfigs)) {
            return $this->jsonFromError($alertConfigs);
        }

        return $this->json(Errors::Ok, $alertConfigs['data']);
    }

    private static function getFieldAlertInfoByName($fieldName, $configs)
    {
        foreach ($configs as $config) {
            if ($fieldName == $config['field_name']) {
                return $config;
            }
        }
        return null;
    }
}