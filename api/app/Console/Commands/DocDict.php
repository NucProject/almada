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
        'deviceId'       => ['int', '设备ID', '12'],

        'groupId'        => ['int', '用户组ID', '3'],
        'groupName'      => ['string', '用户组名称', '中检维康'],
        'groupDesc'      => ['string', '用户组描述', '中检维康管理员分组'],

        'maxFlow'        => ['float', '最大累积流量', '123.456'],
        'timeBegin'      => ['int', '起始时间', '1493357200'],
        'timeEnd'        => ['int', '结束时间', '1493358200'],

        'saved'          => ['int', '是否保存(1保存成功, 0保存失败)', '1'],


        'stationName'    => ['string', '自动站名称', '北京全自动站'],
        'stationDesc'    => ['string', '自动站描述', '北京全自动站(房山)'],
        'stationAddress' => ['string', '自动站地址', '房山XX-XX'],
        'stationType'    => ['int', '自动站类型', '1'],

        'dataId'         => ['int', '数据项ID', '1'],
        'dataTime'       => ['int', '数据项时间', '1500000000'],

        'deviceName'     => ['string', '设备名称', '高压电离室'],
        'deviceDesc'     => ['string', '设备描述', '高压电离室'],
        'deviceType'     => ['string', '设备类型', '高压电离室'],

        'fieldName'     => ['string', '字段名称', 'doserate'],
        'fieldDesc'     => ['string', '字段描述', '剂量率'],
        'fieldTitle'    => ['string', '字段显示名称', '剂量率'],

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