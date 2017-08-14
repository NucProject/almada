<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/6/27
 * Time: 上午9:47
 */

namespace App\Http\Controllers;


use App\Services\Errors;
use App\Services\HpgeService;
use Illuminate\Http\Request;

class HpgeController extends Controller
{
    /**
     * @param Request $request
     * @param $deviceId
     * @return array
     *
     * @cat hpge
     * @title HPGE数据查询接口
     * @comment HPGE数据查询接口
     * @url-param deviceId || int || 设备ID ||
     * @url-param sid || string || SID ||
     * @ret-val list.0.dataTime
     * @ret-val list.0.someFieldValue
     *
     * @ret-val pager.currentPage
     * @ret-val pager.totalPage
     */
    public function query(Request $request, $deviceId)
    {
        $sid = $request->input('sid', '');

        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        $result = HpgeService::queryData($deviceId, [$timeBegin, $timeEnd], $sid);
        if (self::isOk($result)) {
            $data = $result['data'];
            return $this->json(Errors::Ok, [
                'list' => $data,
                'pager' => []
            ]);
        }
    }

    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat hpge
     * @title HPGE核素数据查询接口
     * @comment 根据HPGE设备ID + SID查询核素(活度+浓度)
     *
     * @url-param deviceId || int || 设备ID
     * @url-param sid || string || SID(采样ID)
     *
     * TODO: / flow 得到浓度
     * @ret-val
     * @ret-val
     * @ret-val
     */
    public function nuclide(Request $request, $deviceId)
    {
        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $sid = $request->input('sid', '');

        $result = HpgeService::queryNuclide($deviceId, $sid);

        if (self::isOk($result)) {
            $data = $result['data'];
            return $this->json(Errors::Ok, [
                'list' => $data,
                'pager' => []
            ]);
        }
    }

    /**
     * @param Request $request
     * @param $deviceId
     * @param $dataId
     * @return string
     *
     * @cat hpge
     * @title HPGE数据文件下载接口
     * @comment 根据设备ID和数据ID下载HPGE数据文件
     */
    public function download(Request $request, $deviceId, $dataId)
    {
        if (!self::isValidId($deviceId) ||
            !self::isValidId($dataId)) {
            return $this->json(Errors::BadArguments);
        }

        $result = HpgeService::getFileInfo($deviceId, $dataId);
        if (self::isOk($result)) {
            $data = $result['data'];


            header("Content-Disposition: attachment; filename={$data['file_name']}");
            header('Content-Type: application/x-download');
            echo file_get_contents($data['file_link']);
            return;
        }

        return $this->jsonFromError($result);
    }

    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat hpge
     * @title HPGE数据文件下载接口
     * @comment 根据设备ID和数据ID下载HPGE数据文件
     */
    public function nuclideHistoryData(Request $request, $deviceId)
    {
        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());

        // TODO:
    }
}