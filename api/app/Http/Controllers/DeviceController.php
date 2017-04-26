<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/26
 * Time: 下午3:19
 */

namespace App\Http\Controllers;


class DeviceController extends Controller
{

    public function devices()
    {

        return $this->json(0, []);
    }
}