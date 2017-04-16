<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/1/16
 * Time: 上午10:05
 */

namespace App\Services;

/**
 * Class ValidateService
 * @package App\Services
 * 简单扩展服务
 *
 * 说明:
 * 1. 目标是让Validator的创建者以最小的成本创建一个Validator(只需要写一个static函数即可)
 * 2. $extendValidators的作用是注册, (注册项保护static函数名和 错误信息), 数组的好处是可以查询, 便于需要校验的时候再使用扩展
 * 3. 把其他和字段校验相关的支持都集中到这里, 例如返回信息, 便于管理
 * 4. 在Controller class中加入validate2, 便于IDE可以进行代码提示.
 */
class ValidateService
{
    /**
     * @var array
     * 此处是扩展的Validator的*注册*处
     */
    public static $extendValidators = [
        'mobile' => ['validateMobile', 'Is not a valid mobile number']
    ];

    /**
     * @param $factory \Illuminate\Contracts\Validation\Factory
     * @param $rules
     * 根据Rules的内容决定扩展哪些Validator
     */
    public static function addExtendValidators($factory, $rules)
    {
        $extendNames = array_keys(self::$extendValidators);
        foreach ($rules as $key => $rule) {
            if (in_array($rule, $extendNames)) {
                $extend = self::$extendValidators[$rule];
                // 创建一个闭包便于转发到普通的static函数上
                $function = function($attribute, $value, $parameters) use($extend) {

                    return call_user_func_array(['\App\Services\ValidateService', $extend[0]], [$attribute, $value, $parameters]);
                };
                $message = $extend[1];
                $factory->extend($rule, $function, $message);
            }
        }
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return int
     * 大陆手机号校验
     * @example: 其他的扩展写成static函数即可, 并添加到$extendValidators中.
     */
    public static function validateMobile($attribute, $value, $parameters)
    {
        return preg_match('/^0?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/', $value);
    }
}