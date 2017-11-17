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
     * @form-param username || string || 用户名
     * @form-param password || string || 密码
     *
     * @ret-val user.userName || string || 用户名
     * @ret-val user.userNick || string || 昵称
     * @ret-val user.userId || int || User ID
     */
    public function register(Request $request)
    {
        $data = $request->input();

        $valid = $this->validate2($data, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($valid->fails()) {
            return $this->json(Errors::BadArguments);
        }

        $foundResult = UserService::findUser($data['username']);
        if (self::isOk($foundResult)) {
            return $this->json(Errors::UserExists);
        }

        $result = UserService::newUser($data);
        if (self::isOk($result)) {
            $user = $result['data'];
            unset($user['user_password']);
            unset($user['create_time']);
            unset($user['update_time']);
            return $this->json(Errors::Ok, ['user' => $user]);
        }

        return $this->jsonFromError($result['data']);
    }


    /**
     * @param Request $request
     *
     * @cat user
     * @title 用户登录
     * @comment 用户登录(服务端会Set-Cookie)
     *
     * @return string
     *
     * @form-param username || string || 用户名
     * @form-param password || string || 密码
     *
     * @ret-val token || string || token
     * @ret-val userId || int || 用户ID
     *
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

        $username = $data['username'];
        $foundResult = UserService::findUser($username);
        if (self::hasError($foundResult)) {
            return $this->jsonFromError($foundResult);
        }

        $user = $foundResult['data'];

        if (!password_verify($data['password'], $user['user_password'])) {
            return $this->json(Errors::UserWrongPassword);
        }
        $userId = $user['user_id'];
        session_start();
        $sid = session_id();
        $request->session()->set('userId', $userId);

        // TODO: Set-Cookie
        return $this->json(Errors::Ok, ['token' => $sid, 'userId' => $userId]);

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
     *
     * @form-param invite || string || 邀请码
     * @form-param password || string || 密码
     *
     */
    public function join(Request $request)
    {
        $invite = $request->input('invite');
        if (!$invite || strlen($invite) != 10) {
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