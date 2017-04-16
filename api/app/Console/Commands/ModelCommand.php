<?php

namespace App\Console\Commands;
use \Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Created by PhpStorm.
 * User: healer
 * Date: 16/12/5
 * Time: 下午11:11
 * Usage:
 *  php artisan model:create bo_%
 *  生成数据库中以bo_开头的表对应的Model
 */
class ModelCommand extends Command
{
    /**
     * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'model:create {tableName}';

    protected $description = 'Generated models against tables';


    /**
     * @return void
     */
    public function handle()
    {
        $multiTables = false;
        $tableName = $this->argument('tableName');
        if (strstr($tableName, '%'))
        {
            $multiTables = true;
        }

        if ($multiTables)
        {
            $tables = self::getTables( str_replace('%', '', $tableName) );
            foreach ($tables as $tableName)
            {
                self::createModel($tableName);
            }
        }
        else
        {
            self::createModel($tableName);
        }


        // TODO: Dump file into 'Models'
        exit;
    }

    protected static function createModel($tableName)
    {
        $tableInfo = self::getTableInfo($tableName);

        /**
         * @var model.php use
         */
        $modelName = ucfirst(Str::camel($tableName));

        $primaryFieldInfo = current(array_filter($tableInfo, function ($field) {
            return ($field->Key == 'PRI');
        }));

        $primaryKey = $primaryFieldInfo->Field;

        ob_start();
        include 'templates/modelbase.php';
        $fileContent = "<?php\n" . ob_get_contents();
        ob_end_clean();

        $appRootPath = dirname(dirname(dirname(__FILE__)));

        // Base models
        $fileName = $appRootPath . '/Models/Base/' . $modelName . 'Base.php';
        file_put_contents($fileName, $fileContent);

        ob_start();
        include 'templates/model.php';
        $fileContent = "<?php\n" . ob_get_contents();
        ob_end_clean();

        $fileName = $appRootPath . '/Models/' . $modelName . '.php';
        if (!file_exists($fileName))
        {
            // Models下面的Model文件只生成一次, 以后不会再被修改, 除非我改生成逻辑
            file_put_contents($fileName, $fileContent);
        }
    }

    private static function getTableInfo($tableName)
    {
        $results = DB::select("show full columns from $tableName");
        return $results;
    }

    private static function getTables($tableNamePattern)
    {
        $results = DB::select("show tables");

        // 关于 Tables_in_open_web_api
        // show tables like "pattern" 执行返回的数据的字段名称比较乱, 要构造Tables_in_open_web_api这个名称
        // 所以自己filter好了.
        $tables = array_map(function($table) {
            return $table->Tables_in_open_web_api;
        }, array_filter($results, function ($table) use($tableNamePattern) {
            return strstr($table->Tables_in_open_web_api, $tableNamePattern) != null;
        }));

        return $tables;
    }
}