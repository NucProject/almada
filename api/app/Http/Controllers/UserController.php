<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/28
 * Time: 下午3:25
 */

namespace App\Http\Controllers;


use App\Services\Errors;
use App\Services\GroupService;
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
            return $this->json(Errors::Ok, ['user' => $result['data']]);
        }

        return $this->jsonFromError($result['data']);
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
            return $this->json(Errors::BadArguments, []);
        }

        $token = null;

        // TODO: Set-Cookie
        return $this->json(Errors::Ok, ['token' => $token]);

    }

    /**
     * @param Request $request
     *
     *
     * @cat user
     * @title 用户申请加入组
     * @comment 用户通过用户组ID申请加入该组
     *
     * @return string
     */
    public function join(Request $request)
    {
        $invite = $request->input('invite');
        if (!$invite) {
            return $this->json(Errors::BadArguments, ['msg' => 'Wrong Invite']);
        }

        $userId = $request->user()->getUid();
        if (!self::isValidId($userId)) {
            return $this->json(Errors::UserNotLogin);
        }

        $result = GroupService::getGroupByInvite($invite);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        $group = $result['data'];

        $result = UserService::joinGroup($userId, $group['group_id']);
        if (self::hasError($result)) {
            return $this->jsonFromError($result);
        }

        return $this->json(Errors::Ok, []);
    }
}