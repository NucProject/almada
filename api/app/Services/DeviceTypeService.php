<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/24
 * Time: 下午3:12
 */

namespace App\Services;


use App\Models\AdDevice;
use App\Models\AdDeviceField;
use App\Models\AdDeviceType;

class DeviceTypeService
{
    use ResultTrait;

    public static function getAllTypes()
    {
        $types = AdDeviceType::query()->get();
        return self::ok($types->toArray());
    }

    public static function createDeviceType($data)
    {
        $type = new AdDeviceType();
        $type->type_name = $data['typeName'];
        $type->type_desc = $data['typeDesc'];
        $type->status = 1;
        if (!$type->save()) {
            return self::error(Errors::SaveFailed);
        }

        return self::ok($type->toArray());
    }

    public static function updateDeviceType($typeId, $data)
    {
        $type = AdDeviceType::query()->where('type_id', $typeId)->first();
        if (!$type) {
            return self::error(Errors::DeviceNotFound); // TODO:
        }

        $type->type_name = $data['typeName'];
        $type->type_desc = $data['typeDesc'];
        $type->status = 1;
        if (!$type->save()) {
            return self::error(Errors::SaveFailed);
        }

        return self::ok($type->toArray());

    }

    /**
     * @param $typeId
     * @param $fields
     *
     * @return array
     */
    public static function updateFields($typeId, $fields)
    {
        if (!is_array($fields)) {
            return self::error(Errors::BadArguments);
        }

        foreach ($fields as $field) {
            $fieldId = 0;
            $fieldObj = false;
            if (array_key_exists('fieldId', $field)) {
                $fieldId = $field['fieldId'];
                if (self::isValidId($fieldId)) {
                    $fieldObj = AdDeviceField::query()->find($fieldId);
                }
            } else {
                $fieldObj = new AdDeviceField();
            }

            if (!$fieldObj) {
                continue;
            }

            $fieldObj->setAttributes($field, false, ['fieldName', 'fieldDesc', 'fieldTitle', 'fieldUnit']);
            $fieldObj->type_id = $typeId;
            $fieldObj->status = 1;
            if (!$fieldObj->save()) {
                return self::error(Errors::SaveFailed);
            }
        }

        return self::ok($fields);
    }

    public static function checkFields($fields)
    {
        foreach ($fields as $field) {
            if (!array_key_exists('fieldName', $field)) {
                return self::error(Errors::BadArguments);
            }
            if (!array_key_exists('fieldTitle', $field)) {
                return self::error(Errors::BadArguments);
            }
        }
        return self::ok([]);
    }

    public static function getDeviceTypeIdArrayByDependTypeId($dependTypeId)
    {
        $types = AdDeviceType::query()
            ->select('type_id')
            ->where('depend_type_id', $dependTypeId)
            ->get()
            ->toArray();

        return self::ok($types);
    }
}