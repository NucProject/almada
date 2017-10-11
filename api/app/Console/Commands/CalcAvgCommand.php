<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/10/11
 * Time: 19:01
 */

namespace App\Console\Commands;


use App\Models\DtData;
use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalcAvgCommand extends Command
{
    /**
     * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'calc:avg {deviceId}';

    protected $description = 'Calculate the em devices 6-minutes-avg data';

    public function handle()
    {
        $deviceId = $this->argument('deviceId');


        while (true) {
            $data = DtData::queryDevice($deviceId)
                ->where('electric_avg', 0.0)
                ->orderBy('data_id', 'desc')
                ->first();

            if ($data) {
                $this->calcAvgValues($deviceId, $data);
            }

            sleep(5);
        }

    }

    /**
     * @param $deviceId
     * @param DtData $data
     */
    private function calcAvgValues($deviceId, $data)
    {
        $avgs = DtData::queryDevice($deviceId)
            ->addSelect(DB::raw('avg(electric) as electric_avg'))
            ->addSelect(DB::raw('sqrt(avg(electric * electric)) as electric_savg'))
            ->addSelect(DB::raw('avg(magnetic) as magnetic_avg'))
            ->addSelect(DB::raw('sqrt(avg(magnetic * magnetic)) as magnetic_savg'))
            ->whereBetween('data_time', [$data->data_time - 6 * 60, $data->data_time])
            ->get()
            ->toArray();

        $data->setTable('dt_data_1');

        if ($data->save($avgs[0])) {

        }
    }


}