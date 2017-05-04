<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/4
 * Time: 下午6:26
 */

namespace App\Http\Controllers;


class DocController extends Controller
{
    /**
     * @cat NoDoc
     */
    public function pages()
    {
        $rootPath = app()->basePath();

        $docPath = "{$rootPath}/storage/docs";

        $docs = $this->listDirTree($docPath);
        foreach ($docs as $doc) {
            if (is_array($doc)) {
                continue;
            }

            if (strstr($doc, '.html') != null) {
                $docName = substr($doc, 0, strlen($doc) - 5);
                echo "<a href='/p/doc/{$docName}'>$doc</a><br>";
            }
        }
        exit;
    }

    /**
     * @param $docName
     * @cat NoDoc
     *
     */
    public function render($docName)
    {
        $rootPath = app()->basePath();

        $docPath = "{$rootPath}/storage/docs/{$docName}.html";
        if (file_exists($docPath)) {
            echo file_get_contents($docPath);
            exit;
        }
        echo "No this doc!";
    }

    private function listDirTree($dirName=null)
    {
        if (empty($dirName))
            exit("IBFileSystem: directory is empty.");
        if (is_dir($dirName)) {
            if ($dh = opendir($dirName)) {
                $tree = [];
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") {
                        $filePath = $dirName . "/" . $file;
                        if (is_dir($filePath)) //为目录,递归
                        {
                            $tree[$file] = $this->listDirTree($filePath);
                        }
                        else //为文件,添加到当前数组
                        {
                            $tree[] = $file;
                        }
                    }
                }
                closedir($dh);
            } else {
                exit("IBFileSystem: can not open directory $dirName.");
            }

            //返回当前的$tree
            return $tree;
        } else {
            exit("IBFileSystem: $dirName is not a directory.");
        }
    }
}