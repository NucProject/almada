<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/4/26
 * Time: 下午3:18
 */

namespace App\Http\Controllers;


use App\Services\AlertService;
use App\Services\DataService;
use App\Services\Errors;
use App\Services\Handlers\FileHandler;
use App\Services\ResultTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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

        $history = $request->input('history', 0);
        $historyAlert = 0;
        if ($history) {
            $historyAlert = $request->input('historyAlert', 0);
        }
        // TODO:
        $saveResult = DataService::save($deviceId, $data);
        if ($this->isOk($saveResult)) {

            $saved = $saveResult['data'];
            // $saved['data'] contains data_id
            if (!$history || $historyAlert) {
                // 非历史数据才报警
                // 以后由Python脚本处理Alert逻辑
                // AlertService::checkDataAlert($saved['data'], $deviceId);
            }
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
    public function upload(Request $request, $deviceId)
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
     * @url-param avg || string || 平均值时间间隔 1m, 5m, 1h, 1d
     * @url-param algo || string || 数字取值算法
     * @url-param tailPadding || int || 是否补齐尾部数据?
     * @url-param fields || string || 字段显示列表(逗号分隔)
     *
     * @ret-val list.0.dataTime
     * @ret-val list.0.someFieldValue
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

        // 是否尾部补齐
        $headPadding = $request->input('headPadding', 1);
        $tailPadding = $request->input('tailPadding', 1);

        // $algo = $request->input('algo', 'avg');
        $avg = $request->input('avg', 'none');
        $order = $request->input('order', '');
        if (!$order) {
            $order = 'asc';
        }

        // 逗号分隔
        $fieldsSet = $request->input('fields', '');

        $result = DataService::queryData($deviceId, [$timeBegin, $timeEnd], $avg, $fieldsSet, $order);
        if (self::isOk($result)) {
            $data = $result['data'];

            $list = self::paddingDataList($avg, $order, $data, [$timeBegin, $timeEnd],
                [
                    'headPadding' => $headPadding,
                    'tailPadding' => $tailPadding,

                ]);
            return $this->json(Errors::Ok, [
                'list' => $list,
                'pager' => []
            ]);
        } elseif (self::hasError($result) && $result['error'] == Errors::ResourceNotFound) {

            $list = self::paddingEmptyDataList($avg, $order, [$timeBegin, $timeEnd],
                [
                    'headPadding' => $headPadding,
                    'tailPadding' => $tailPadding,
                    'dataTemplate' => $result['data']['template']

                ]);
            return $this->json(Errors::Ok, [
                'list' => $list,
                'pager' => []
            ]);
        }

        return $this->jsonFromError($result);
    }

    /**
     * @param $avg
     * @param $order
     * @param $data
     * @param $timeRange
     * @param array $options
     * @return array
     */
    private static function paddingDataList($avg, $order, $data, $timeRange, $options=[])
    {
        if (count($data) <= 1) {
            return $data;
        }

        // TODO: 时区计算得动态化
        date_default_timezone_set('PRC');

        $step = 1;
        $firstTime = date('Y-m-d H:i', $timeRange[0]);
        $lastTime = date('Y-m-d H:i', $timeRange[1]);

        if ($avg == '5m') {
            $step = 300;
            $firstTime = date('Y-m-d H:i', intval($timeRange[0] / 300) * 300);
            $lastTime = date('Y-m-d H:i', intval($timeRange[1] / 300) * 300);
        } elseif ($avg == '1h') {
            $step = 3600;
            $firstTime = date('Y-m-d H:00', $timeRange[0]);
            $lastTime = date('Y-m-d H:00', $timeRange[1]);
        } elseif ($avg == '1d') {
            $step = 3600 * 24;
            $firstTime = date('Y-m-d 00:00', $timeRange[0]);
            $lastTime = date('Y-m-d 00:00', $timeRange[1]);
        } elseif ($avg == '1m') {
            $step = 60;
            $firstTime = date('Y-m-d H:i', $timeRange[0]);
            $lastTime = date('Y-m-d H:i', $timeRange[1] );
        } else {
            return $data;
        }

        if ($order == 'desc') {
            $step = -$step;
        }

        $list = [];

        $first = $data[0];
        $temp = [];
        foreach ($first as $key => $value) {
            $temp[$key] = '-';
        }
        $lastAvgDataTime = strtotime($firstTime);

        $lastAvgDataOffset = 0;

        if (array_key_exists('tailPadding', $options) && $options['tailPadding']) {
            // 只有最后一个数据的时间不是结束时间的时候, 才追加一个空对象到尾部
            if (strtotime(end($data)['avg_data_time']) != strtotime($lastTime)) {
                $temp['avg_data_time'] = $lastTime;
                array_push($data, $temp);
            }
        }

        foreach ($data as $item) {

            $avgDataTime = strtotime($item['avg_data_time']);

            $avgDataTimeDiff = $avgDataTime - $lastAvgDataTime;
            if ($avgDataTimeDiff == 0) {
                $lastAvgDataTime = $avgDataTime;
                $lastAvgDataOffset = 1;
                $list[] = $item;
                continue;
            }
            if ($avgDataTimeDiff != $step) {
                $times = $avgDataTimeDiff / $step;

                for ($i = $lastAvgDataOffset; $i < $times; $i++) {

                    $null = $temp;
                    $dataTime = $lastAvgDataTime + $i * $step;

                    $null['avg_data_time'] = date('Y-m-d H:i', $dataTime);
                    $null['data_time'] = $dataTime; //strtotime($null['avg_data_time']);
                    $list[] = $null;
                }
            }


            $item['data_time'] = strtotime($item['avg_data_time']);
            $list[] = $item;

            $lastAvgDataTime = $avgDataTime;
            $lastAvgDataOffset = 1;
        }
        return $list;
    }

    /**
     * @param $avg
     * @param $order
     * @param $timeRange
     * @param array $options
     * @return array
     */
    private static function paddingEmptyDataList($avg, $order, $timeRange, $options=[])
    {
        // TODO: 时区计算得动态化
        date_default_timezone_set('PRC');

        $step = 1;
        $firstTime = date('Y-m-d H:i', $timeRange[0]);
        $lastTime = date('Y-m-d H:i', $timeRange[1]);

        if ($avg == '5m') {
            $step = 300;
            $firstTime = date('Y-m-d H:i', intval($timeRange[0] / 300) * 300);
            $lastTime = date('Y-m-d H:i', intval($timeRange[1] / 300) * 300);
        } elseif ($avg == '1h') {
            $step = 3600;
            $firstTime = date('Y-m-d H:00', $timeRange[0]);
            $lastTime = date('Y-m-d H:00', $timeRange[1]);
        } elseif ($avg == '1d') {
            $step = 3600 * 24;
            $firstTime = date('Y-m-d 00:00', $timeRange[0]);
            $lastTime = date('Y-m-d 00:00', $timeRange[1]);
        } else {
            $step = 300;
            $firstTime = date('Y-m-d H:i', intval($timeRange[0] / 300) * 300);
            $lastTime = date('Y-m-d H:i', intval($timeRange[1] / 300) * 300);
        }

        if ($order == 'desc') {
            $step = -$step;
        }

        $list = [];
        $template = $options['dataTemplate'];
        $temp = [];
        foreach ($template as $key => $value) {
            $temp[$key] = '-';
        }

        $firstAvgDataTime = strtotime($firstTime);
        $lastAvgDataTime = strtotime($lastTime);

        $time = $firstAvgDataTime;
        for (; $time <= $lastAvgDataTime; $time += $step) {
            $item = $temp;
            $item['avg_data_time'] = date('Y-m-d H:i', $time);
            $item['data_time'] = $time; //$step;
            $list[] = $item;


        }
        return $list;
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

    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat data
     * @title 获取设备数据报警信息
     * @comment 获取设备数据报警信息(可以指定时间范围)
     *
     * @url-param deviceId || int || 设备ID
     * @url-param timeBegin || int || 开始时间
     * @url-param timeEnd  || int || 结束时间 ||
     */
    public function alerts(Request $request, $deviceId)
    {
        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        $alertsResult = AlertService::getDataAlert($deviceId, [$timeBegin, $timeEnd], []);

        if (self::isOk($alertsResult)) {
            $alerts = $alertsResult['data'];

            return $this->json(Errors::Ok, $alerts);
        }
        return $this->json(Errors::Ok, []);

    }


    /**
     * @param Request $request
     * @param $deviceId
     * @return string
     *
     * @cat data
     * @title 获取设备数据获取率
     * @comment 获取设备数据获取率(可以指定时间范围)
     *
     * @url-param deviceId || int || 设备ID
     * @url-param timeBegin || int || 开始时间
     * @url-param timeEnd  || int || 结束时间 ||
     */
    public function ratio(Request $request, $deviceId)
    {
        $timeBegin = $request->input('timeBegin', 0);
        // TODO: Parse time if in some format?
        $timeEnd = $request->input('timeEnd', time());
        $ratioResult = DataService::getDeviceDataRatio($deviceId, [$timeBegin, $timeEnd], []);

        if (self::isOk($ratioResult)) {
            $ratios = $ratioResult['data'];

            if ($request->input('padding', 0)) {
                $ratios = DataService::paddingRatios($ratios, [$timeBegin, $timeEnd]);
            }

            return $this->json(Errors::Ok, ['list' => $ratios]);
        }
        return $this->json(Errors::Ok, []);

    }
}