<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/7/10
 * Time: 上午9:01
 */

namespace App\Services;


use App\Models\AdCommand;

class CommandService
{
    use ResultTrait;

    const HistoryCommand = 1;

    // 命令状态
    const CmdStt_Sent = 1;
    const CmdStt_Fetched = 2;
    const CmdStt_Executed = 3;

    /**
     * @param $deviceId
     * @param $timeRange
     * @return array
     */
    public static function addHistoryCommand($deviceId, $timeRange)
    {
        $cmd = new AdCommand();
        $cmd->target_id = $deviceId;
        $cmd->command_status = self::CmdStt_Sent;
        $cmd->command_type = self::HistoryCommand;
        // p1 is begin-time, p2 is end-time
        $cmd->command_p1 = $timeRange[0];
        $cmd->command_p2 = $timeRange[1];

        if ($cmd->save()) {
            return self::ok($cmd->toArray());
        }

        return self::error(Errors::SaveFailed);
    }

    public static function fetchDeviceHistoryCommand($deviceId)
    {
        $cmd = AdCommand::query()
            ->where('command_type', self::HistoryCommand)
            ->where('target_id', $deviceId)
            ->where('command_status', self::CmdStt_Sent)
            ->orderBy('command_id')
            ->first();


        if ($cmd) {
            $command = $cmd->toArray();

            $cmd->command_status = self::CmdStt_Fetched;
            if ($cmd->save()) {
                return self::ok([$command['command_p1'], $command['command_p2']]);
            } else {
                return self::error(Errors::SaveFailed);
            }
        }

        return self::error(Errors::BadArguments);

    }
}