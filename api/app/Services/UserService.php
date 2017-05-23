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
        $password = $data['password'];


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

        if ($user->group_id > 0) {
            return self::error(Errors::UserStateError, ['msg' => '已经加入其它组']);
        }

        $user->group_id = $groupId;
        if (!$user->save()) {
            return self::error(Errors::SaveFailed, ['msg' => '保存失败']);
        }

        return self::ok($user->toArray());
    }
}