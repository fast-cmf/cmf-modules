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
        
        // 如果是模块视图
        if (strpos($name, '::') !== false) {
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
        
        $path = $this->getThemeViewPath($theme) . '/' . str_replace('.', '/', $name) . '.blade.php';
        
        if (file_exists($path)) {
            return $path;
        }
        
        throw new \InvalidArgumentException("View [{$name}] not found in theme [{$theme}].");
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
     * 获取主题视图路径
     */
    protected function getThemeViewPath($theme)
    {
        return config('themes.path', public_path('themes')) . '/' . $theme . '/' . config('themes.structure.views', 'views');
    }
} 