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

    const UserNotLogin = 7;

    const UserStateError = 8;

    const UserWrongPassword = 9;

    const UserExists = 10;

    const ResourceNotFound = 11;

    private static $errorsMap = [
        self::Ok           => ['msg' => 'OK', 'comment' => '成功'],
        self::BadArguments => ['msg' => 'Bad arguments', 'comment' => '参数错误'],
        self::SaveFailed   => ['msg' => 'Model saved failed', 'comment' => '保存失败'],
        self::NoDataTime   => ['msg' => 'No dataTime field', 'comment' => '没有dataTime字段'],
        self::DeviceNotFound   => ['msg' => 'No this device', 'comment' => '设备未找到'],
        self::GroupNotFound   => ['msg' => 'No this group', 'comment' => '没有这个用户组'],
        self::UserNotFound   => ['msg' => 'No this user', 'comment' => '没有这个用户'],
        self::UserNotLogin   => ['msg' => 'User not login', 'comment' => '用户没有登录'],
        self::UserStateError   => ['msg' => 'User state error', 'comment' => '用户状态错误'],
        self::UserWrongPassword => ['msg' => 'Wrong password', 'comment' => '用户名或密码错误'],
        self::UserExists => ['msg' => 'User exists', 'comment' => '用户已经存在'],
        self::ResourceNotFound => ['msg' => 'Resource not found', 'comment' => '资源不存在'],
    ];

    public static function getErrorMsg($status)
    {
        if (array_key_exists($status, self::$errorsMap)) {
            return self::$errorsMap[$status]['msg'];
        }
        return '';
    }
}