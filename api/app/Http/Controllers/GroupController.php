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
use Illuminate\Http\Request;

class GroupController extends Controller
{
    use ResultTrait;
    /**
     * @cat group
     * @title 创建组
     * @comment 创建组
     *
     * @param Request $request
     * @return string
     * @form-param groupName || string || 用户组名称
     * @form-param groupDesc || string || 用户组描述 ||
     * @ret-val group.groupId
     * @ret-val group.groupName
     * @ret-val group.groupDesc
     */
    public function create(Request $request)
    {
        $data = $request->input();

        $valid = $this->validate2($data, ['groupName' => 'required|string']);
        if ($valid->fails()) {

        }

        $result = GroupService::create($data);
        if (self::isOk($result)) {
            $group = $request['data'];
            return $this->json(Errors::Ok, $group);
        }
    }

    /**
     * @cat group
     * @title 用户组列表
     * @comment 用户组列表
     *
     * @param Request $request
     * @return string
     */
    public function groups(Request $request)
    {
        $options = [];

        $result = GroupService::getGroups($options);
        if (self::isOk($result)) {
            $groups = $request['data'];
            $data = ['list' => $groups];
            return $this->json(Errors::Ok, $data);
        }

    }

    /**
     * @param Request $request
     * @param $groupId
     * @return string
     *
     * @cat group
     * @title 用户组用户列表
     * @comment 用户组用户列表
     *
     */
    public function users(Request $request, $groupId)
    {
        if (!ResultTrait::isValidId($groupId) && $groupId != 'all') {
            return $this->json(Errors::BadArguments, []);
        }

        $result = GroupService::getUsers($groupId);
    }


}