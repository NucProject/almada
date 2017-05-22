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
            $group->group_name = $data['groupDesc'];
        }

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
}