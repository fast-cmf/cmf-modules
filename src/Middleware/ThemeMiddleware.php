<?php

namespace Fastcmf\Modules\Middleware;

use Closure;
use Illuminate\Http\Request;
use Fastcmf\Modules\Theme;

class ThemeMiddleware
{
    /**
     * 处理传入请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $themeName
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $themeName = null)
    {
        // 如果指定了主题名称，则设置当前主题
        if ($themeName) {
            Theme::set($themeName);
        }

        return $next($request);
    }
} 