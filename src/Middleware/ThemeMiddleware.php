<?php

namespace Fastcmf\Modules\Middleware;

use Closure;
use Fastcmf\Modules\Facades\Theme;

class ThemeMiddleware
{
    /**
     * 处理请求
     */
    public function handle($request, Closure $next)
    {
        // 获取当前主题
        $themeName = $this->getCurrentTheme($request);
        
        // 设置当前主题
        Theme::set($themeName);
        
        // 检查主题是否存在
        $themePath = config('themes.path', public_path('themes')) . '/' . $themeName;
        if (!is_dir($themePath)) {
            // 如果主题不存在，使用默认主题
            $defaultTheme = config('themes.default', 'default');
            Theme::set($defaultTheme);
        }
        
        return $next($request);
    }

    /**
     * 获取当前主题
     */
    private function getCurrentTheme($request)
    {
        // 1. 从请求参数中获取主题
        $theme = $request->query('theme');
        if ($theme) {
            session(['theme' => $theme]);
            return $theme;
        }
        
        // 2. 从会话中获取主题
        $theme = session('theme');
        if ($theme) {
            return $theme;
        }
        
        // 3. 使用默认主题
        return config('themes.default', 'default');
    }
} 