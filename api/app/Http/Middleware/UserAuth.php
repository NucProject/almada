<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/23
 * Time: 上午8:59
 */

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Closure;


class UserAuth
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $debug = $request->header('_debug', 0);

        if ($debug) {

        }

        // $s = $request->session();//->get("d");
        // $a = $s->get("myname");
        // echo "($a)";
        // $s->flush();

        $request->setUserResolver(function() use ($request) {
            $user = new User();
            $user->setUid(1);
            return $user;
        });

        return $next($request);
    }
}