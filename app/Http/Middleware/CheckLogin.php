<?php

namespace App\Http\Middleware;

use App\Model\User;
use Closure;

class CheckLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //判断token是否存在
        $token=$request->input('token');
        $user_info=User::where('remember_token',$token)->select(['id','remember_token'])->first();
        if($user_info){
            //如果存在，获取用户id并存进session
            $request->session()->put('id',$user_info->id);
            return $next($request);
        }else{
            //如果不存在，session置空，token就不用管了，因为每次登录都回自动跟新token
            $request->session()->flush();
            //并且返回re=3
            return redirect()->action(
                'ReturnController@returnStandard', ['re' => 3]
            );
        }
    }
}
