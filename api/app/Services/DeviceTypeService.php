<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/24
 * Time: 下午3:12
 */

namespace App\Services;


use App\Models\AdDevice;
use App\Models\AdDeviceType;

class DeviceTypeService
{
    use ResultTrait;

    public static function getAllTypes()
    {
        $types = AdDeviceType::query()->get();
        return self::ok($types->toArray());
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

        }


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
    }
}