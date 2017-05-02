<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/26
 * Time: 下午3:18
 */

namespace App\Services;


class Errors
{
    const Ok = 0;

    const BadArguments = 1;

    const SaveFailed = 2;

    const NoDataTime = 3;

    const DeviceNotFound = 4;

    const GroupNotFound = 5;

    const UserNotFound = 6;


    private static $errorsMap = [
        self::Ok           => ['msg' => 'OK', 'comment' => '成功'],
        self::BadArguments => ['msg' => 'OK', 'comment' => '参数错误'],
        self::SaveFailed   => ['msg' => 'Model saved failed', 'comment' => '保存失败'],
        self::NoDataTime   => ['msg' => 'No dataTime field', 'comment' => '没有dataTime字段'],
        self::DeviceNotFound   => ['msg' => 'No this device', 'comment' => '设备未找到'],
        self::GroupNotFound   => ['msg' => 'No this group', 'comment' => '没有这个用户组'],
        self::UserNotFound   => ['msg' => 'No this user', 'comment' => '没有这个用户'],

    ];

    public static function getErrorMsg($status)
    {
        if (array_key_exists($status, self::$errorsMap)) {
            return self::$errorsMap[$status]['msg'];
        }
        return '';
    }
}