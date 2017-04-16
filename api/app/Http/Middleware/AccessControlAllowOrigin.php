<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 16/12/15
 * Time: 上午9:58
 */

namespace App\Http\Middleware;

use Closure;

/**
 * Class AccessControlAllowOrigin
 * @package App\Http\Middleware
 *
 */
class AccessControlAllowOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $allowOriginList = explode(',', env('ALLOW_ORIGIN'));
        $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, $allowOriginList)) {
            header('Access-Control-Allow-Origin:' . $origin);
        } else {
            header('Access-Control-Allow-Origin:*');
        }

        header('Access-Control-Allow-Credentials:true');
        header('Access-Control-Allow-Headers:X-Requested-With,Content-Type');
        header('Access-Control-Allow-Methods:GET,POST,HEAD,OPTIONS');
        header('Content-Type:application/json; charset=utf-8');
        return $next($request);
    }
}