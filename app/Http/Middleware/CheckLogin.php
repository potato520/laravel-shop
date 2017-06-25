<?php

namespace App\Http\Middleware;

use Closure;

class CheckLogin
{
    /**
     * 返回请求过滤器
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 上次访问的页面
        $http_referer = $_SERVER['HTTP_REFERER'];

        $member = $request->session()->get('member', '');
        if($member == ''){
            return redirect('/login?return_url=' . urlencode($http_referer));
        }

        return $next($request);
    }

}