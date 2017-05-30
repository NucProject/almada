<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/26
 * Time: 下午3:19
 */

namespace App\Http\Controllers;


use App\Models\AdDevice;
use App\Services\DeviceService;
use App\Services\Errors;
use App\Services\ResultTrait;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * @cat device
     * @title 设备列表
     * @comment 设备列表
     *
     * @param Request $request
     * @return string
     */
    public function devices(Request $request)
    {
        $query = AdDevice::query();

        $groupId = $request->input('groupId', 0);
        $typeId = $request->input('typeId', 0);

        if (!self::isValidId($groupId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Group id is required']);
        }

        if (!self::isValidId($typeId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Type id is required']);
        }

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $devices = $query->get()->toArray();

        $data = [
            'list' => $devices
        ];
        return $this->json(0, $data);
    }

    /**
     * @cat device
     * @title 创建设备
     * @comment 创建设备
     *
     * @param Request $request
     * @return string
     */
    public function create(Request $request)
    {
        $groupId = $request->input('groupId', 0);
        if (!ResultTrait::isValidId($groupId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Group id is required']);
        }

        $typeId = $request->input('typeId', 0);
        if (!self::isValidId($typeId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Type id is required']);
        }

        DeviceService::createDevice($groupId, $typeId);
    }

}