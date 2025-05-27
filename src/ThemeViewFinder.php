<?php

namespace Fastcmf\Modules;

use Illuminate\View\FileViewFinder;
use Fastcmf\Modules\Facades\Theme;

class ThemeViewFinder extends FileViewFinder
{
    /**
     * 查找视图文件
     */
    public function find($name)
    {
        // 如果是主题视图
        if (strpos($name, 'theme::') === 0) {
            return $this->findThemeView($name);
        }
        
        // 如果是后台主题视图
        if (strpos($name, 'admin::') === 0) {
            return $this->findAdminThemeView($name);
        }
        
        // 如果是模块视图
        if (strpos($name, '::') !== false) {
            // 检查是否是后台视图
            if (strpos($name, 'admin.') !== false) {
                return $this->findAdminModuleView($name);
            }
            return $this->findModuleView($name);
        }
        
        // 默认视图查找
        return parent::find($name);
    }
    
    /**
     * 查找主题视图
     */
    protected function findThemeView($name)
    {
        $theme = Theme::current()->getName();
        $name = str_replace('theme::', '', $name);
        
        // 检查是否是直接路径
        if (strpos($name, '/') === 0) {
            $path = $this->getThemeViewPath($theme) . $name . '.blade.php';
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // 解析视图路径
        $parts = explode('.', $name);
        
        // 如果是应用视图
        if (count($parts) >= 1) {
            $module = $parts[0];
            
            // 如果是应用的公共视图
            if (count($parts) >= 3 && $parts[1] === 'public') {
                $viewName = $parts[2];
                $path = $this->getThemeViewPath($theme) . '/' . $module . '/public/' . $viewName . '.blade.php';
                
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            // 如果是应用的控制器视图
            if (count($parts) >= 3) {
                $controller = $parts[1];
                $method = $parts[2];
                $path = $this->getThemeViewPath($theme) . '/' . $module . '/' . $controller . '/' . $method . '.blade.php';
                
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
        
        // 常规路径查找
        $path = $this->getThemeViewPath($theme) . '/' . str_replace('.', '/', $name) . '.blade.php';
        
        if (file_exists($path)) {
            return $path;
        }
        
        throw new \InvalidArgumentException("View [{$name}] not found in theme [{$theme}].");
    }
    
    /**
     * 查找后台主题视图
     */
    protected function findAdminThemeView($name)
    {
        $theme = Theme::adminCurrent()->getName();
        $name = str_replace('admin::', '', $name);
        
        // 检查是否是直接路径
        if (strpos($name, '/') === 0) {
            $path = $this->getThemeViewPath($theme) . $name . '.blade.php';
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // 解析视图路径
        $parts = explode('.', $name);
        
        // 如果是应用视图
        if (count($parts) >= 1) {
            $module = $parts[0];
            
            // 如果是公共视图
            if ($module === 'public' || (count($parts) >= 2 && $parts[1] === 'public')) {
                $viewName = $module === 'public' ? $parts[1] : $parts[2];
                $path = $this->getThemeViewPath($theme) . '/public/' . $viewName . '.blade.php';
                
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            // 如果是应用的控制器视图
            if (count($parts) >= 3) {
                $controller = 'admin_' . $parts[1];
                $method = $parts[2];
                $path = $this->getThemeViewPath($theme) . '/' . $module . '/' . $controller . '/' . $method . '.blade.php';
                
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
        
        // 常规路径查找
        $path = $this->getThemeViewPath($theme) . '/' . str_replace('.', '/', $name) . '.blade.php';
        
        if (file_exists($path)) {
            return $path;
        }
        
        throw new \InvalidArgumentException("View [{$name}] not found in admin theme [{$theme}].");
    }
    
    /**
     * 查找模块视图
     */
    protected function findModuleView($name)
    {
        list($module, $view) = explode('::', $name);
        $theme = Theme::current()->getName();
        
        // 解析视图路径
        $viewParts = explode('.', $view);
        
        // 如果是控制器视图
        if (count($viewParts) >= 2) {
            $controller = $viewParts[0];
            $method = $viewParts[1];
            
            // 构建视图路径：theme/default/blog/index/index.blade.php
            $themePath = $this->getThemeViewPath($theme) . '/' . $module . '/' . $controller . '/' . $method . '.blade.php';
            
            if (file_exists($themePath)) {
                return $themePath;
            }
        }
        
        // 如果是公共视图
        if ($viewParts[0] === 'public') {
            // 移除public前缀
            array_shift($viewParts);
            $publicView = implode('/', $viewParts);
            
            // 构建视图路径：theme/default/blog/public/xxx.blade.php
            $themePath = $this->getThemeViewPath($theme) . '/' . $module . '/public/' . $publicView . '.blade.php';
            
            if (file_exists($themePath)) {
                return $themePath;
            }
        }
        
        // 如果主题中没有，则尝试使用默认视图查找
        return parent::find($name);
    }
    
    /**
     * 查找后台模块视图
     */
    protected function findAdminModuleView($name)
    {
        list($module, $view) = explode('::', $name);
        $theme = Theme::adminCurrent()->getName();
        
        // 从视图名称中移除admin.前缀
        $view = str_replace('admin.', '', $view);
        
        // 解析视图路径
        $viewParts = explode('.', $view);
        
        // 如果是控制器视图
        if (count($viewParts) >= 2) {
            $controller = 'admin_' . $viewParts[0];
            $method = $viewParts[1];
            
            // 构建视图路径：theme/admin_default/blog/admin_index/index.blade.php
            $themePath = $this->getThemeViewPath($theme) . '/' . $module . '/' . $controller . '/' . $method . '.blade.php';
            
            if (file_exists($themePath)) {
                return $themePath;
            }
        }
        
        // 如果是公共视图
        if ($viewParts[0] === 'public') {
            // 移除public前缀
            array_shift($viewParts);
            $publicView = implode('/', $viewParts);
            
            // 构建视图路径：theme/admin_default/public/xxx.blade.php
            $themePath = $this->getThemeViewPath($theme) . '/public/' . $publicView . '.blade.php';
            
            if (file_exists($themePath)) {
                return $themePath;
            }
        }
        
        // 如果后台主题中没有，则尝试使用默认视图查找
        return parent::find($name);
    }
    
    /**
     * 获取主题视图路径
     */
    protected function getThemeViewPath($theme)
    {
        return config('themes.path', public_path('themes')) . '/' . $theme . '/' . config('themes.structure.views', 'views');
    }
} 