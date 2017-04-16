<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 16/12/7
 * Time: 下午1:31
 */

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ValidScope
 * @package App\Models
 *
 * 根据现在的数据库设计, 每一个表都会有一个status字段, 来表达非业务的数据有效性
 */
class ValidScope implements Scope
{
    /**
     * @param Builder $builder
     * @param Model $model
     * @return $this
     * 通常我们只查询status!=0的记录
     */
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where('status', '!=', 0);
    }
}