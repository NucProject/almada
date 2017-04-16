<?php

use Illuminate\Support\Str;

ob_start();

define('ENV_FILE', '.env.unit');

require_once(dirname(__FILE__) . '/TestCase.php');


/**
 * Created by PhpStorm.
 * User: healer_kx@163.com
 * Date: 2017/1/5
 * Time: 下午1:40
 */
class AutoSmokingTest extends TestCase
{
    private $methodGetCount = 0;

    private $methodPostCount = 0;

    private $caseCount = 0; // TODO: 统计Cases的数量

    private $commonHeaders = [];

    //不需要测试的接口
    private $noTestApi = [
        '/bo/v1/web/photo/upload',
        '/bo/v1/web/file/upload',
        '/bo/v1/private/errorStatus',
        '/bo/v1/web/developer/app/{appId}/publish',
        '/bo/v1/private/tool/list',
        '/bo/v1/private/app/info/{appId}'
    ];

    public function setUp()
    {
        parent::setUp();
        // 公共的Headers(解决dev, admin, merchant的登录验证问题)
        $this->commonHeaders = [
        ];
        // 数据重置
        $this->dataResetNew();
    }

    /**
     * 通过PHP array进行数据初始化
     */
    private function dataResetNew()
    {
        require_once(dirname(__FILE__) . '/auto_smoking_test_db.php');
        $dataSet = new DataSet();
        foreach ($dataSet->tables as $table) {
            $emptyTableSql = 'TRUNCATE TABLE ' . $table;
            DB::statement($emptyTableSql);
        }

        foreach ($dataSet->sqlArray as $tableName => $tableData) {
            foreach ($tableData as $record) {
                $fields = implode(',', array_keys($record));
                $valuesArray = [];
                foreach ($record as $v) {
                    if (is_string($v)) {
                        $str = '\'' . $v . '\'';
                        array_push($valuesArray, $str);
                    } else {
                        array_push($valuesArray, $v);
                    }
                }
                $values = implode(',', $valuesArray);
                $insertSql = 'INSERT INTO ' . $tableName . '(' . $fields . ') VALUES (' . $values . ');';
                DB::statement($insertSql);
            }
        }
    }

    /**
     * @return bool
     * 通过SQLs进行数据初始化
     */
    private function dataReset()
    {
        $sqlPath = dirname(__FILE__) . '/auto_smoking_test_db.sql';
        if (!file_exists($sqlPath)) {
            return false;
        }

        $sqlArray = [];
        $sqlStr = '';
        $handle = fopen($sqlPath, "r");
        if ($handle) {
            while (!feof($handle)) {
                $buffer = trim(fgets($handle));
                if (substr($buffer, 0, 2) == '--' || $buffer == '') {
                    $sqlStr = '';
                } elseif (substr($buffer, -1) == ';') {
                    array_push($sqlArray, $sqlStr . $buffer);
                    $sqlStr = '';
                } else {
                    $sqlStr .= $buffer;
                }
            }
        }
        foreach ($sqlArray as $v) {
            DB::statement($v);
        }
    }

    private function getApiRoutes()
    {
        // 得到它的路由信息
        $routes = $this->app->getRoutes();

        return $routes;
    }

    public function testAll()
    {
        $routes = $this->getApiRoutes();

        echo "\nStart all APIs auto smoking test cases\n";

        foreach ($routes as $route) {
            $urlMethod = $route['method'];
            $uriPattern = $route['uri'];
            // Remove optional URL params
            $uriPattern = preg_replace('/\[.*\]/', '', $uriPattern);
            if (in_array($uriPattern, $this->noTestApi)) {
                continue;
            }

            $action = $route['action']['uses'];

            list($controllerName, $methodName) = explode('@', $action);

            try {

                $controller = new \ReflectionClass($controllerName);

                $method = $controller->getMethod($methodName);
                if ($method) {

                    $this->doApiSmokingTest($urlMethod, $uriPattern, $method->getDocComment());
                }
            } catch(\Exception $e) {
                echo "$controllerName @ $methodName\n";
                var_dump($e->getFile());
                var_dump($e->getLine());
                var_dump($e->getMessage());
                var_dump($e->getCode());
                exit;
            }
        }

        echo "\n";
        echo "Auto smoking test-cases report:\n";
        echo "\tGET APIs count={$this->methodGetCount}\n";
        echo "\tPOST APIs count={$this->methodPostCount}\n";
        $allCasesCount = $this->methodGetCount + $this->methodPostCount;
        echo "\tAll APIs count={$allCasesCount}\n";
    }


    private function doApiSmokingTest($urlMethod, $uriPattern, $doc)
    {
        $docLines = self::parseDocToLines($doc);
        $params = self::generateParams($docLines);//获取请求参数

        if ($urlMethod == 'GET') {
            $this->doGetApiSmokingTest($urlMethod, $uriPattern, $params);
            $this->methodGetCount += 1;
        } elseif ($urlMethod == 'POST') {
            $this->doPostApiSmokingTest($urlMethod, $uriPattern, $params);
            $this->methodPostCount += 1;
        }
    }

    private function doGetApiSmokingTest($urlMethod, $uriPattern, $params)
    {
        foreach ($params as $value) {
            $url = $this->printUrl($urlMethod, $uriPattern, $value);
            // 如果@case指定了header, 则不需要commonHeaders的设置
            $headers = !empty($value['headerParams']) ? $value['headerParams'] : $this->commonHeaders;

            $this->get($url, $headers)->seeJson(['status' => $value['expectParams']['status']]);
            $returnData = $this->response->getContent();
            if (!empty($value['dbParams'])) {
                $this->validateDbValues($value['dbParams']);
            }

            // TODO: Re-run the case with Redis support, then compare the data info;
            // 问题1, 怎么在UT开始的时候,把必要的数据导入到Redis中?
        }
    }

    private function doPostApiSmokingTest($urlMethod, $uriPattern, $params)
    {
        foreach ($params as $value) {
            $url = $this->printUrl($urlMethod, $uriPattern, $value);
            $data = $value['postParams'];
            // 如果@case指定了header, 则不需要commonHeaders的设置
            $headers = !empty($value['headerParams']) ? $value['headerParams'] : $this->commonHeaders;

            $this->post($url, $data, $headers)->seeJson(['status' => $value['expectParams']['status']]);
            $returnData = $this->response->getContent();
            if (!empty($value['dbParams'])) {
                $this->validateDbValues($value['dbParams']);
            }

            // TODO: Re-run the case with Redis support, then compare the data info;
        }
    }

    /**
     * @title 输出url
     * @param $urlMethod
     * @param $uriPattern
     * @param array $params
     * @return string
     */
    private function printUrl($urlMethod, $uriPattern, $params)
    {
        $setUrlParams = !empty($params['urlParams']) ? $params['urlParams'] : [];
        // 根据URL和参数,拼装最终的URI
        $url = self::buildUri($uriPattern, $setUrlParams);
        // 输出到终端
        echo "{$urlMethod}\t{$url}\n";

        return $url;
    }

    /**
     * @title 获取case参数
     * @param array $docLines
     * @return array
     */
    private static function generateParams(array $docLines)
    {
        // case的格式是  @case PARAM1=... @form FORM-PARAM=... @expect VALUE=...
        $results = [];
        foreach ($docLines as $line) {
            $params = [];
            if (Str::startsWith($line, '@case')) {

                // 获取get参数
                $urlParamStr = self::getCharBetween($line, '@case');
                $params['urlParams'] = self::getParamsToArray($urlParamStr);

                // 获取post参数
                $postParamStr = self::getCharBetween($line, '@form');
                $params['postParams'] = self::getParamsToArray($postParamStr);

                // 获取期望得到的结果
                $expectParamStr = self::getCharBetween($line, '@expect');
                $params['expectParams'] = self::getParamsToArray($expectParamStr);
                if (!isset($params['expectParams']['status'])) {
                    $params['expectParams']['status'] = 200;
                }

                // 获取header参数
                $headerParamStr = self::getCharBetween($line, '@header');
                $params['headerParams'] = self::getParamsToArray($headerParamStr);

                // 数据验证
                $dbParamStr = self::getCharBetween($line, '@db');
                $params['dbParams'] = self::getParamsToArray($dbParamStr);

                array_push($results, $params);
            }
        }
        if (empty($results)) {
            $results[0]['urlParams'] = [];
            $results[0]['postParams'] = [];
            $results[0]['expectParams'] = ['status' => 200];
            $results[0]['headerParams'] = [];
            $results[0]['dbParams'] = [];
        }

        return $results;
    }

    private static function getCharBetween($str, $start, $end = "@")
    {
        $part = explode($start, $str);
        if (isset($part[1])) {
            $results = trim(explode($end, $part[1])[0]);

            return $results;
        }

        return '';
    }

    private static function getParamsToArray($paramsStr)
    {
        $params = [];
        if (!empty($paramsStr)) {
            $paramsArray = explode('||', $paramsStr);
            foreach ($paramsArray as $k => $paramLine) {
                if (Str::startsWith(trim($paramLine), '{')) { // 若为json字符串
                    $params[$k] = json_decode(trim($paramLine), true);
                } else {
                    $assign = explode('=', $paramLine);
                    if (count($assign) > 1) {
                        if (substr(trim($assign[0]), -2) == '[]') {
                            $assign[0] = substr_replace(trim($assign[0]), '', -2);
                            $params[$assign[0]] = json_decode(trim($assign[1]));
                        } else {
                            $params[trim($assign[0])] = trim($assign[1]);
                        }
                    }
                }
            }
        }

        return $params;
    }

    /**
     * @title 验证数据库是否与期待一致
     * @param array $params
     */
    private function validateDbValues($params)
    {
        foreach ($params as $value) {
            if (!isset($value['field']) || !isset($value['table']) || !isset($value['condition'])) {
                echo 'field and table and condition must required in @db';
                exit;
            }

            $validParams = [];
            // case里定义的需要验证的字段值
            $validParamArray = explode('&', $value['field']);
            foreach ($validParamArray as $paramLine) {
                $assign = explode('=', $paramLine);
                if (count($assign) > 1) {
                    $validParams[trim($assign[0])] = trim($assign[1]);
                }
            }

            // 数据库实际存储的字段值
            $selectField = implode(',', array_keys($validParams));
            $sql = "select " . $selectField . " from " . $value['table'] . " where " . $value['condition'];
            $data = json_decode(json_encode(DB::select($sql)), true);

            foreach ($data as $k => $v) {
                $r = array_diff($v, $validParams);
                if (!empty($r)) {
                    echo "data test error" . "\n";
                    print_r($value);
                    exit;
                }
            }
        }
    }

    private static function parseDocToLines($docStr)
    {
        return array_map(
            function ($line) {
                return trim($line, " \t*");
            },
            explode("\n", $docStr));
    }

    /**
     * @param $uriPattern
     * @param array $urlParams
     * @return string
     */
    private static function buildUri($uriPattern, array $urlParams)
    {
        $url = preg_replace_callback('/{(\w+)}/',
            function ($m) use (&$urlParams) {
                if ($m) {
                    $paramName = $m[1];
                    if (array_key_exists($paramName, $urlParams)) {
                        $paramVal = $urlParams[$paramName];
                        unset($urlParams[$paramName]);

                        return $paramVal;
                    }
                }

                return 0;
            },
            $uriPattern);

        $first = true;
        foreach ($urlParams as $paramName => $paramVal) {
            $url .= ($first ? '?' : '&') . "{$paramName}={$paramVal}";
            $first = false;
        }

        return $url;
    }

    private static function afterSkip($line, $skip)
    {
        return trim(substr($line, strlen($skip)));
    }

    /**
     * @param $d1
     * @param $d2
     * @return boolean
     *
     * TODO: 比较两个json返回值data的部分,如果一致,说明接口在Redis的支持下,结果和没有Redis的时候是一样的.
     */
    private static function compareData($d1, $d2)
    {

        return true;
    }
}