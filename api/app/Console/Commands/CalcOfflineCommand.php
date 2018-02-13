<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2018/2/13
 * Time: 下午12:21
 */

namespace App\Console\Commands;


use App\Models\AdDataAlert;
use App\Models\DtData;
use App\Services\Errors;
use App\Services\RedisService;
use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalcOfflineCommand extends Command
{
    /**
     * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'calc:offline';

    protected $description = '';

    private static function addOfflineAlert($deviceId)
    {
        $alert = new AdDataAlert();
        $alert->device_id = $deviceId;
        $alert->alert_level = 101;  // Offline
        $alert->data_id = 0;
        $alert->field_id = 0;
        $alert->status = 1;
        if ($alert->save()) {
            RedisService::setOfflineAlert($deviceId);
        }
    }

    public function handle()
    {
        $allLatestData = RedisService::getAllLatestData();

        $now = time();
        foreach ($allLatestData as $deviceId => $latestData) {

            $data = json_decode($latestData, true);
            $interval = $now - $data['dataTime'];
            if ($interval > 30) {
                self::addOfflineAlert($deviceId);
            }
        }

    }
}