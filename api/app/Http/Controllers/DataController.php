<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/26
 * Time: 下午3:18
 */

namespace App\Http\Controllers;


use App\Services\DataService;
use App\Services\Errors;
use App\Services\Handlers\FileHandler;
use App\Services\ResultTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class DataController extends Controller
{
    /**
     * @cat data
     * @title 数据接收接口
     * @comment 数据接收接口
     *
     * @url-param deviceId || int || 设备ID
     *
     * @form-param history || int || 是否是历史数据(默认值为0, 表示实时数据; 1表示历史数据)
     * @form-param data || array || Form表单Array, data[]={dataTime=UNIX-TIME, 其他字段值}, 参见调用示例
     *
     * @ret-val saved.deviceId || int || 保存成功的信息 || 1
     *
     * @param Request $request
     * @param int $deviceId
     * @return string
     *
     * @status 0 || OK
     *
     * @case deviceId=1 @form data[]=?
     *
     * @example-begin JavaScript
     * d = new FormData()
     * d.append("data[0][v1]", 1)
     * d.append("data[0][v2]", 2)
     * d.append("data[0][dataTime]", 1)
     * // 支持多个数据包
     * d.append("data[1][v1]", 2)
     * d.append("data[1][v2]", 1)
     * d.append("data[1][dataTime]", 1)
     *
     * var xhr = new XMLHttpRequest();
     * xhr.open("POST", "http://host:port/d/send/1");
     * xhr.send(d);
     * @example-end
     *
     * @example-begin
     * curl -d "" ...
     * @example-end
     */
    public function send(Request $request, $deviceId)
    {
        if (!ResultTrait::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Bad deviceId']);
        }

        $data = $request->input('data', []);
        if (!$data || !is_array($data)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Bad data']);
        }

        //var_dump($data);exit;

        $history = $request->input('history', 0);
        // TODO:

        $saveResult = DataService::save($deviceId, $data);
        if ($this->isOk($saveResult)) {

            $saved = $saveResult['data'];
            return $this->json(Errors::Ok, ['saved' => $saved]);
        }

        return $this->jsonFromError($saveResult);
    }

    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat data
     * @title 文件上传
     * @comment 文件上传接口
     *
     * @url-param file || File || 文件
     * @url-param fileType || string || 文件类型
     *
     * @ret-val fileLink || string || 文件链接(文件路径)
     * @ret-val fileName || string || 文件名
     */
    public function file(Request $request, $deviceId)
    {
        if (!ResultTrait::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments, ['msg' => 'Bad deviceId']);
        }

        $file = $request->file('file');

        $fileType = $request->input('fileType', '');

        $handler = FileHandler::getHandler($fileType);
        if ($handler) {
            $result = $handler->save($request, $file, $deviceId);
            if (self::isOk($result)) {
                return $this->json(Errors::Ok, $result['data']);
            }

            return $this->jsonFromError($result);
        }

        return $this->json(Errors::BadArguments, ['msg' => 'No file handler for ' . $fileType]);
    }

    /**
     * @param Request $request
     * @param int $deviceId
     * @return string
     *
     * @cat data
     * @title 数据查询接口
     * @comment 数据查询接口
     *
     * @url-param deviceId || int || 设备ID
     * @url-param timeBegin || int || 开始时间
     * @url-param timeEnd  || int || 结束时间 ||
     * @url-param avg || string || 平均值时间间隔 5m, 1h, 1d
     * @url-param algo || string || 数字取值算法
     *
     * @ret-val list.0.dataTime
     * @ret-val list.0.someFieldValue
     *
     * @ret-val pager.currentPage
     * @ret-val pager.totalPage
     *
     */
    public function query(Request $request, $deviceId)
    {
        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        // $algo = $request->input('algo', 'avg');
        $avg = $request->input('avg', 'none');

        $result = DataService::queryData($deviceId, [$timeBegin, $timeEnd], $avg);
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
     * @param int $deviceId
     * @return string
     *
     * @cat data
     * @title 最新数据查询接口
     * @comment 最新数据查询接口(最新的一条)
     *
     * @url-param deviceId || int || 设备ID
     *
     *
     * @ret-val list.0.dataTime
     * @ret-val list.0.someFieldValue
     *
     * @ret-val pager.currentPage
     * @ret-val pager.totalPage
     *
     */
    public function latest(Request $request, $deviceId)
    {
        if (!self::isValidId($deviceId)) {
            return $this->json(Errors::BadArguments);
        }

        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        if ($timeBegin > $timeEnd) {
            return $this->json(Errors::BadArguments);
        }

        // $algo = $request->input('algo', 'avg');
        $avg = $request->input('avg', '5m');

        $result = DataService::latestData($deviceId, [$timeBegin, $timeEnd], $avg);
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
     * @return array
     *
     * @cat data
     * @title 数据文件下载
     * @comment 数据文件下载
     * @url-param p || string || 文件路径
     *
     * @ret-val File || File || 文件
     */
    public function download(Request $request)
    {
        $path = $request->input('p');
        $fileName = base_path('storage/static') . $path;
        if (file_exists($fileName)) {
            header('content-type', 'text/x-component');
            readfile($fileName);
        } else {
            return $this->json(Errors::BadArguments);
        }
    }
}