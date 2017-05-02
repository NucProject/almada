<?php
/**
 * Created by PhpStorm.
 * User: healer_kx@163.com
 * Date: 16/12/15
 * Time: 下午4:56
 */

namespace App\Console\Commands;


class DocPage
{
    private $apiList = [];

    public function addApi($api)
    {
        $this->apiList[] = $api;
    }

    public function save($fileName)
    {
        $file = fopen($fileName, 'wb');
        $this->writeToc($file);
        $index = 1;
        foreach ($this->apiList as $api)
        {
            $this->writeApi($file, $api, $index);
            $index += 1;
        }

        fclose($file);
        return true;
    }

    /**
     * @param $file
     */
    private function writeToc($file)
    {
        ob_start();
        $host = ''; // TODO:
        include 'templates/apidoc_toc.php';
        $tocContent = ob_get_contents();
        ob_end_clean();

        fwrite($file, $tocContent);
    }

    /**
     * @param $file
     * @param $api
     * @param $index
     */
    private function writeApi($file, $api, $index)
    {
        $doc = $api;
        $doc['index'] = $index;
        ob_start();
        include 'templates/apidoc.php';
        $fileContent = ob_get_contents();
        ob_end_clean();

        fwrite($file, $fileContent);
    }
}