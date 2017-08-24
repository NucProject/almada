<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/8/22
 * Time: 22:24
 */

namespace App\Http\Controllers;


use App\Services\ResultTrait;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    use ResultTrait;

    /**
     * @param Request $request
     * @param $deviceId
     *
     * @cat
     * @title
     * @comment
     */
    public function setAlertConfig(Request $request, $deviceId)
    {

    }
}