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

        }

        $deviceTypeId = $request->input('deviceTypeId', 0);
        if (!ResultTrait::isValidId($deviceTypeId)) {

        }

        DeviceService::createDevice($groupId, $deviceTypeId);
    }

}