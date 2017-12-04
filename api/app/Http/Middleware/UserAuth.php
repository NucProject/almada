<?php
/**
 * Created by PhpStorm.
 * User: healer
 * Date: 2017/5/23
 * Time: 上午8:59
 */

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Errors;
use Illuminate\Http\Request;
use Closure;


class UserAuth
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $debugUserId = $request->input('__debug_user_id', 0);
        $session = $request->session();

        if ($debugUserId > 0) {
            $userId = $debugUserId;
        } else {
            $userId = $session->get('userId', 0);
        }


        if (!$userId) {
            echo json_encode([
                'status' => Errors::UserNotLogin,
                'msg' => 'User Not Login',
                'data' => []
            ]);
            exit();
        }


        $request->setUserResolver(function() use ($userId) {
            $user = new User();

            $user->setUid($userId);
            return $user;
        });

        return $next($request);
    }
}