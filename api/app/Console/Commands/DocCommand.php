<?php
/**
 * Created by PhpStorm.
 * User: healer_kx@163.com
 * Date: 16/12/7
 * Time: 下午9:07
 */

namespace App\Console\Commands;

use \Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class DocCommand
 * @package App\Console\Commands
 * Usage:
 *  php artisan doc:generate % > ~/a.html
 *  % 表示全部的, 但是从工程实践上, 建议使用path的一部分作为分类.
 */
class DocCommand extends Command
{
    protected $signature = 'doc:generate {path}';

    protected $description = 'doc generator';

    protected $lookup;

    private static $catDocMap = [];

    public function handle()
    {
        $allPaths = false;
        $pathPattern = $this->argument('path');
        if ($pathPattern == '%')
        {
            $allPaths = true;
        }

        $this->lookup = 'App\Console\Commands\DocDict::getInfoByName';

        // 启动一个App
        $app = require dirname(dirname(dirname(__DIR__))) .'/bootstrap/app.php';

        // 得到它的路由信息
        $routes = $app->getRoutes();
        if (!$allPaths)
        {
            $pathPatterns = explode(',', $pathPattern);
            $routes = array_filter($routes, function($route) use ($pathPatterns) {
                foreach ($pathPatterns as $pathPattern)
                {
                    if (strstr($route['uri'], $pathPattern) != null)
                        return true;
                }
                return false;
            });
        }
        $this->generateDoc($routes);

        // system("python storage/docs/auto_publish/auto_publish.py");
    }

    /**
     * @param $routes array
     */
    private function generateDoc(array $routes)
    {
        // 生成APIs部分
        $counter = 1;
        foreach ($routes as $route)
        {
            $urlMethod = $route['method'];
            $uriPattern = $route['uri'];
            if (!array_key_exists('uses', $route['action'])) {
                continue;
            }
            $action = $route['action']['uses'];

            list($controllerName, $methodName) = explode('@', $action);

            try {
                $counter += 1;
                $controller = new \ReflectionClass($controllerName);

                $method = $controller->getMethod($methodName);
                if ($method) {
                    $this->convertDocToWiki($urlMethod, $uriPattern, $method->getDocComment());

                }
            } catch (\Exception $e) {
                echo "$controllerName, $methodName\n";
                var_dump($e->getLine());

                var_dump($e->getMessage());
                var_dump($e->getCode());
            }
        }

        echo "Count=$counter\n";

        foreach (self::$catDocMap as $cat => $page)
        {
            $page->save("storage/docs/$cat.html");
        }
    }

    /**
     * @param $httpMethod
     * @param $uriPattern
     * @param $docStr
     * @return string doc
     *
     */
    private function convertDocToWiki($httpMethod, $uriPattern, $docStr)
    {
        $doc = self::parseDocStr($docStr);

        $doc['httpMethod'] = $httpMethod;
        $doc['url'] = $uriPattern;

        $cat = $doc['cat']; //Category!
        if (array_key_exists($cat, self::$catDocMap)) {
            $page = self::$catDocMap[$cat];
            $page->addApi($doc);
        } else {
            $page = new DocPage();
            $page->addApi($doc);
            self::$catDocMap[$cat] = $page;
        }
        return $doc;
    }

    private function parseDocStr($docStr)
    {
        $docLines = array_map(
            function($line) { return trim($line, " \t*"); },
            explode("\n", $docStr));

        $doc = [
            'title' => "",
            'httpMethod' => '',
            'comment' => "",
            'url' => '',
            'urlParams' => [],
            'formParams' => [],
            'retFields' => [],
            'statusEnum' => [],
            'retVal' => self::convertToHtml([])
        ];

        foreach ($docLines as $line)
        {
            if (Str::startsWith($line, '@title')) {
                $doc['title'] = self::afterSkip($line, '@title');
            } elseif (Str::startsWith($line, '@comment')) {
                $doc['comment'] = self::afterSkip($line, '@comment');
            } elseif (Str::startsWith($line, '@cat')) {
                $doc['cat'] = self::afterSkip($line, '@cat');
            } elseif (Str::startsWith($line, '@url-param')) {
                $urlParamLine = self::afterSkip($line, '@url-param');
                $parts = explode('||', $urlParamLine);
                $urlParam = [
                    'name' => trim($parts[0]),
                    'type' => trim($parts[1]),
                    'comment' => trim($parts[2]),
                    'required' => count($parts) > 3 ? 'No' : 'Yes'
                ];

                $doc['urlParams'][] = $urlParam;
            } elseif (Str::startsWith($line, '@form-param')) {
                $formParamLine = self::afterSkip($line, '@form-param');
                $parts = explode('||', $formParamLine);
                $formParam = [
                    'name' => trim($parts[0]),
                    'type' => trim($parts[1]),
                    'comment' => trim($parts[2]),
                    'required' => count($parts) > 3 ?  'No' : 'Yes'
                ];

                $doc['formParams'][] = $formParam;
            } elseif (Str::startsWith($line, '@ret-val')) {
                $retValLine = self::afterSkip($line, '@ret-val');
                $parts = explode('||', $retValLine);

                if (count($parts) == 1) {
                    $retName = $parts[0];
                    $nameParts = explode('.', $retName);
                    $info = call_user_func_array($this->lookup, [end($nameParts)]);
                    if ($info) {
                        $parts[1] = $info[0];
                        $parts[2] = $info[1];
                        $parts[3] = $info[2];
                    }
                }

                $retVal = [
                    'name' => trim($parts[0]),
                    'type' => trim($parts[1]),
                    'comment' => trim($parts[2]),
                    'value' => isset($parts[3]) ? trim($parts[3]) : "None",
                ];

                $doc['retFields'][] = $retVal;
            } elseif (Str::startsWith($line, '@status')) {
                $retValLine = self::afterSkip($line, '@status');
                $parts = explode('||', $retValLine);
                $status = [
                    'value' => trim($parts[0]),
                    'comment' => trim($parts[1]),
                ];

                $doc['statusEnum'][] = $status;
            }
        }

        $arrayMaker = new ArrayMaker($doc['retFields']);
        $array = $arrayMaker->makeArray();

        $doc['retVal'] = self::convertToHtml($array);

        return $doc;
    }

    private static function afterSkip($line, $skip)
    {
        return trim(substr($line, strlen($skip)));
    }

    private static function convertToHtml($data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $json = preg_replace("/\n/", '<br>', $json);
        $json = preg_replace("/ /", '&nbsp;', $json);
        // 此处不能使用/\\s/, 而是 / /, 注意此处的空格, 否则UTF8写入有问题

        return $json;
    }

}