<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/28
 * Time: 上午8:21
 */

namespace App\Http\Controllers;


use App\Services\Errors;

class CommandController extends Controller
{
    /**
     * @cat cmd
     * @title 获取历史数据补齐命令
     * @comment 自动站查询数据中心是否有下发历史数据补齐命令
     *
     * @return string
     *
     *
     */
    public function history()
    {
        $commends = [];
        $commends[] = ['type' => 'history', 'timeBegin' => 1, 'timeEnd' => 10];
        return $this->json(Errors::Ok, ['list' => $commends]);
    }
}