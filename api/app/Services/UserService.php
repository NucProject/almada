<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/18
 * Time: 下午4:40
 */

namespace App\Services;


use App\Models\AdUser;

class UserService
{
    use ResultTrait;
    /**
     * @param $data
     *
     *
     * @return array
     */
    public static function newUser($data)
    {
        $username = $data['username'];
        $nickname = isset($data['nickname']) ? $data['nickname'] : '';
        $password = $data['password'];

        $user = new AdUser();
        $user->status = 1;
        $user->user_name = $username;
        $user->user_nick = $nickname;
        $user->user_password = password_hash($password, PASSWORD_DEFAULT);
        if (!$user->save()) {
            return self::error(Errors::SaveFailed);
        }

        return self::ok($user->toArray());
    }

    /**
     * @param $username
     * @return array
     */
    public static function findUser($username)
    {
        $user = AdUser::query()->where('user_name', $username)->first();
        if ($user) {
            return self::ok($user->toArray());
        }

        return self::error(Errors::UserNotFound);
    }

    /**
     * @param $userId
     * @param $groupId
     * @return array
     */
    public static function joinGroup($userId, $groupId)
    {
        $user = AdUser::query()->find($userId);
        if (!$user) {
            return self::error(Errors::UserNotFound, ['msg' => '没有这个用户']);
        }

        if ($user->group_id > 0 && $user->group_id != $groupId) {
            return self::error(Errors::UserStateError, ['msg' => '已经加入其它组']);
        }

        $user->group_id = $groupId;
        if (!$user->save()) {
            return self::error(Errors::SaveFailed, ['msg' => '保存失败']);
        }

        return self::ok(['groupId' => $groupId, 'join' => 1]);
    }
}