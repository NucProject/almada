<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/28
 * Time: 下午3:25
 */

namespace App\Http\Controllers;


use App\Services\ResultTrait;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @param Request $request
     *
     * @cat user
     * @title 用户注册
     * @comment 用户注册
     *
     * @return string
     *
     *
     * @form-param username || string || 用户名
     * @form-param password || string || 密码
     */
    public function register(Request $request)
    {
        $data = $request->input();

        $valid = $this->validate2($data, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($valid->fails()) {

        }

        $groupId = $request->input('groupId', 0);
        if (!ResultTrait::isValidId($groupId)) {

        }


        $result = UserService::newUser($data);
        if (self::isOk($result)) {

        }


    }


    /**
     * @param Request $request
     *
     * @cat user
     * @title 用户登录
     * @comment 用户登录
     *
     * @return string
     *
     *
     * @form-param username || string || 用户名
     * @form-param password || string || 密码
     */
    public function login(Request $request)
    {
        $data = $request->input();

        $valid = $this->validate2($data, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($valid->fails()) {

        }

    }

    /**
     * @param Request $request
     * @param $groupId
     *
     * @cat user
     * @title 用户申请加入组
     * @comment 用户通过用户组ID申请加入该组
     *
     * @return string
     */
    public function applyForGroup(Request $request, $groupId)
    {

    }
}