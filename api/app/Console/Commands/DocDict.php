<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 16/12/13
 * Time: 下午9:42
 */

namespace App\Console\Commands;

/**
 * Class DocDict
 * @package App\Console\Commands
 * 放置该项目的常用字段描述
 */
class DocDict
{
    static $map = [
        // APP 相关
        'deviceId'      => ['int', '设备ID', '12'],

        'groupId'      => ['int', '用户组ID', '3'],
        'groupName'      => ['string', '用户组名称', '中检维康'],
        'groupDesc'      => ['string', '用户组描述', '中检维康管理员分组'],

        'saved'         => ['int', '是否保存(1保存成功, 0保存失败)', '1'],

    ];

    public static function getInfoByName($word)
    {
        if (array_key_exists($word, self::$map))
        {
            return self::$map[$word];
        }
        return ['<Unknown>', '<Unknown>', '???'];
    }
}