<?php
namespace App\Services\Handlers;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: heale
 * Date: 2017/3/10
 * Time: 23:30
 */
abstract class FileHandler
{
    /**
     * @param $fileType
     * @return FileHandler
     */
    public static function getHandler($fileType)
    {
        if ($fileType == 'hpge')
        {
            return new HpgeReportFileHandler();
        }
    }
    public static function checkPath($path)
    {
        $join = 'upload';
        $parts = explode('/', $path);
        foreach ($parts as $part)
        {
            $join = $join . '/' . $part;
            if (!file_exists($join))
            {
                mkdir($join);
            }
        }
        return $join;
    }
    /**
     * @param Request $request
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $deviceId
     * @return array
     */
    public abstract function save(Request $request, $file, $deviceId);
}