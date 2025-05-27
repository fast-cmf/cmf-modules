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
        
        // 先检查主题中的模块视图
        $themePath = $this->getThemeViewPath($theme) . '/modules/' . $module . '/' . str_replace('.', '/', $view) . '.blade.php';
        
        if (file_exists($themePath)) {
            return $themePath;
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
        
        // 先检查后台主题中的模块视图
        $themePath = $this->getThemeViewPath($theme) . '/modules/' . $module . '/' . str_replace('.', '/', $view) . '.blade.php';
        
        if (file_exists($themePath)) {
            return $themePath;
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