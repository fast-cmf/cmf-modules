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
        
        // 获取当前后台主题
        $adminThemeName = $this->getCurrentAdminTheme($request);
        
        // 设置当前后台主题
        session(['current_admin_theme' => $adminThemeName]);
        
        // 检查后台主题是否存在
        $adminThemePath = config('themes.path', public_path('themes')) . '/' . $adminThemeName;
        if (!is_dir($adminThemePath)) {
            // 如果后台主题不存在，使用默认后台主题
            $defaultAdminTheme = config('themes.admin_default', 'admin_default');
            session(['current_admin_theme' => $defaultAdminTheme]);
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
    
    /**
     * 获取当前后台主题
     */
    private function getCurrentAdminTheme($request)
    {
        // 1. 从请求参数中获取后台主题
        $theme = $request->query('admin_theme');
        if ($theme) {
            session(['admin_theme' => $theme]);
            return $theme;
        }
        
        // 2. 从会话中获取后台主题
        $theme = session('admin_theme');
        if ($theme) {
            return $theme;
        }
        
        // 3. 使用默认后台主题
        return config('themes.admin_default', 'admin_default');
    }
} 