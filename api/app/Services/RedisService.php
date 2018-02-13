<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2018/2/9
 * Time: 上午9:53
 */

namespace App\Services;


class RedisService
{

    private static $redis = null;

    /**
     * @return \Redis
     */
    public static function getConnection()
    {
        if (!self::$redis) {
            self::$redis = new \Redis();
            self::$redis->connect('localhost');
        }
        return self::$redis;
    }

    public static function setLatestData($deviceId, $data)
    {
        $redis = self::getConnection();
        $redis->hSet("latest-data", $deviceId, json_encode($data));
        return true;
    }

    public static function getLatestData($deviceId)
    {
        $redis = self::getConnection();
        $value = $redis->hGet("latest-data", $deviceId);
        if ($value) {
            return json_decode($value, true);
        }
        return false;
    }

    public static function getAllLatestData()
    {
        $redis = self::getConnection();
        return $redis->hGetAll("latest-data");
    }

    public static function setOfflineAlert($deviceId)
    {
        $redis = self::getConnection();
        $redis->hSet("offline-devices", $deviceId, time());
    }

    public static function getOfflineAlerts()
    {
        $redis = self::getConnection();
        return $redis->hGetAll("offline-devices");
    }

}