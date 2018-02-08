<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 16/12/8
 * Time: 下午12:47
 */

namespace App\Services;

/**
 * Class BaseService
 * @package App\Services
 */
trait ResultTrait
{
    public static function isOk($retVal)
    {
        return $retVal['error'] === 0;
    }

    public static function hasError($retVal)
    {
        return $retVal['error'] !== 0;
    }

    public static function error($errorCode, $data=[])
    {
        return ['error' => $errorCode, 'data' => $data];
    }

    public static function ok($data)
    {
        return ['error' => 0, 'data' => $data];
    }

    public static function isValidId($objectId)
    {
        return $objectId && is_numeric($objectId) && (intval($objectId) > 0);
    }
}