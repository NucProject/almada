<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/18
 * Time: 下午4:34
 */

namespace App\Services;


use App\Models\AdGroup;

class GroupService
{
    use ResultTrait;

    /**
     * @param $data
     * @return array
     */
    public static function create($data)
    {
        $group = new AdGroup();
        if (array_key_exists('groupName', $data)) {
            $group->group_name = $data['groupName'];
        }

        if (array_key_exists('groupDesc', $data)) {
            $group->group_desc = $data['groupDesc'];
        }

        // 创建组的时候生成邀请码
        $group->group_invite = substr(md5($group->group_name . time()), 4, 16);

        $group->status = 1;
        if (!$group->save()) {
            return self::error(Errors::SaveFailed);
        }

        return self::ok($group->toArray());
    }

    /**
     * @param array $options
     * @return array
     */
    public static function getGroups(array $options)
    {
        $query = AdGroup::query();
        if ($options) {

        }

        $groups = $query->get();

        return self::ok($groups->toArray());
    }

    /**
     * @param $groupId
     * @return \App\Models\AdGroup;
     *
     */
    public static function find($groupId)
    {
        return AdGroup::query()->find($groupId)->first();
    }

    public static function getGroupByInvite($invite)
    {
        $group = AdGroup::query()->where('group_invite', $invite)->first();
        if (!$group) {
            return self::error(Errors::GroupNotFound, ['msg' => 'Bad invitation']);
        }

        return self::ok($group->toArray());
    }

    /**
     * @param $groupId
     * @return array
     */
    public static function getUsers($groupId)
    {
        // TODO:
    }
}