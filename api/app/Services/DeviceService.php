<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/29
 * Time: 下午7:27
 */

namespace App\Services;


use App\Models\AdDevice;
use App\Models\AdDeviceField;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeviceService
{
    use ResultTrait;

    // Field-types supported
    const FieldType_Int = 1;
    const FieldType_Double = 2;
    const FieldType_Boolean = 3;
    const FieldType_String = 4;

    /**
     * @param $deviceId
     * @return \App\Models\Base\AdDeviceBase;
     */
    public static function getDeviceById($deviceId)
    {
        $device = AdDevice::query()->where('device_id', $deviceId)->first();
        if ($device) {
            return self::ok($device->toArray());
        }
        return self::error(Errors::BadArguments);
    }

    /**
     * @param $groupId
     * @param $stationId
     * @return array
     */
    public static function getDevices($groupId, $stationId=0)
    {
        $query = AdDevice::query();

        if (self::isValidId($groupId)) {
            $query->where('group_id', $groupId);
        }

        if (self::isValidId($stationId)) {
            $query->where('station_id', $stationId);
        }

        $devices = $query->get()->toArray();
        return self::ok($devices);
    }

    /**
     * @param int $stationId
     * @param int $typeId
     * @param int $deviceId
     * @param array $data
     *
     * @return array
     */
    public static function createDevice($stationId, $typeId, $deviceId, $data=[])
    {
        $stationResult = StationService::findStationById($stationId);

        if (self::hasError($stationResult)) {
            return self::error(Errors::BadArguments);
        }

        $station = $stationResult['data'];

        $device = new AdDevice();
        $device->station_id = $stationId;
        $device->group_id = $station['group_id'];
        $device->type_id = $typeId;
        $device->depend_device_id = $deviceId;

        $device->setAttributes($data, false, ['deviceName', 'movable']);
        $device->status = 1;

        if (!$device->save()) {
            return self::error(Errors::SaveFailed, ['msg' => 'Device create failed']);
        }

        // 创建数据表
        $tableResult = self::createDeviceTable($device->device_id, $typeId, $device->movable);
        if (self::hasError($tableResult)) {
            return self::error(Errors::SaveFailed, ['msg' => 'Create data table failed']);
        }

        return self::ok($device->toArray());
    }

    /**
     * @param $deviceId
     * @param $typeId
     * @param $movable
     * @return array
     */
    public static function createDeviceTable($deviceId, $typeId, $movable)
    {
        $tableName = "dt_data_{$deviceId}";
        // TODO: Error handling


        Schema::create($tableName, function(Blueprint $table) use ($typeId, $movable) {
            $table->engine = 'InnoDB';
            $table->increments('data_id');
            $table->integer('data_time');
            self::setDataFields($table, $typeId);
            if ($movable) {
                self::setMovableFields($table, $typeId);
            }
            $table->integer('status');
            $table->integer('create_time');
            $table->integer('update_time');
        });

        return self::ok([]);
    }

    /**
     * @param Blueprint $table
     * @param $typeId
     */
    public static function setDataFields(Blueprint $table, $typeId)
    {
        // Read the type fields from $typeId
        $fields = AdDeviceField::query()->where('type_id', $typeId)->get()->toArray();
        foreach ($fields as $field) {
            $fieldName = $field['field_name'];
            if ($field['field_type'] == self::FieldType_Int) {
                $table->integer($fieldName);
            } elseif ($field['field_type'] == self::FieldType_Double) {
                $table->double($fieldName);
            } elseif ($field['field_type'] == self::FieldType_Boolean) {
                $table->tinyInteger($fieldName);
            }  elseif ($field['field_type'] == self::FieldType_String) {
                $table->string($fieldName);
            }
        }
    }

    /**
     * @param Blueprint $table
     */
    public static function setMovableFields(Blueprint $table)
    {
        $table->decimal('lng', 10, 7);
        $table->decimal('lat', 10, 7);
    }
}