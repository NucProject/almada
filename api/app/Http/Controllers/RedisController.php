<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2018/2/9
 * Time: 上午8:51
 */

namespace App\Http\Controllers;


use App\Services\Errors;
use Illuminate\Http\Request;

class RedisController extends Controller
{

    public function setValue(Request $request)
    {
        $redis = new \Redis();
        $redis->connect('localhost');

        $key = $request->input('key');
        $redis->set($key, $request->input('value'));

        $value = $redis->get($key);

        return $this->json(Errors::Ok, ['data' => $value]);
    }
}