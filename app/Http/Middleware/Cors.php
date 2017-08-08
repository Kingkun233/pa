<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
//        // 设置允许访问的域地址
//        $domains = ['https://diningx.cn'];
//        // 判断请求头中是否包含ORIGIN字段
//        if(isset($request->server()['HTTP_ORIGIN'])){
//            $origin = $request->server()['HTTP_ORIGIN'];
//            if (in_array($origin, $domains)) {
//                //设置响应头信息
//                header('Access-Control-Allow-Origin: '.$origin);
//                header('Access-Control-Allow-Headers: Origin, Content-Type, Authorization');
//            }
//        }
//        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
        header('Access-Control-Allow-Credentials: true');
        return $next($request);
    }
}
