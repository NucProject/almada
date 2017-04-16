<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 通用功能
 * @date 2016-12-28
 */
class UtilService
{
    //格式化分页
    public static function formatPage(LengthAwarePaginator $pager)
    {
        $page = array();
        $page['lastPage'] = $pager->lastPage();
        $page['currentPage'] = $pager->currentPage();
        $page['perPage'] = $pager->perPage();
        $page['nextPageUrl'] = $pager->nextPageUrl();
        $page['previousPageUrl'] = $pager->previousPageUrl();
        $page['hasMorePages'] = $pager->hasMorePages();
        $page['count'] = $pager->count();
        $page['totalCount'] = $pager->total();
        
        return $page;
    }
}