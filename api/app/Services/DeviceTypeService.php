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

    /**
     * @param array $typeIdArray
     * @return array
     */
    public static function getAllTypes($typeIdArray=[])
    {
        $query = AdDeviceType::query();

        if (!empty($typeIdArray)) {
            $query->whereIn('type_id', $typeIdArray);
        }

        $types = $query->get();
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

    /**
     * @param $typeId
     * @param array $filters
     * @return array
     */
    public static function getFieldsByTypeId($typeId, $filters=[])
    {
        $query = AdDeviceField::query()
            ->where('type_id', $typeId);

        if ($filters) {
            foreach ($filters as $filter) {
                $query->where($filter['field'], $filter['value']);
            }
        }

        $fields = $query->get()->toArray();

        return self::ok($fields);
    }

    public static function getTypeTitle($typeId)
    {
        $type = AdDeviceType::query()->find($typeId);
        if ($type) {
            return self::ok($type->type_title);
        }
        return self::error(Errors::BadArguments);
    }
}