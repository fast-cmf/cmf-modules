<?php

namespace Fastcmf\Modules\View;

use Illuminate\View\FileViewFinder;
use Fastcmf\Modules\Theme;

class ThemeViewFinder extends FileViewFinder
{
    /**
     * 当前主题
     */
    protected $theme;

    /**
     * 主题视图路径
     */
    protected $themePaths = [];

    /**
     * 设置当前主题
     */
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;
        
        $themePath = $theme->getThemePath() . '/' . config('themes.structure.views', 'views');
        
        if (is_dir($themePath)) {
            $this->themePaths = [$themePath];
            
            // 将主题路径添加到视图路径前面，以便优先查找
            $this->paths = array_merge($this->themePaths, $this->paths);
        }
        
        return $this;
    }

    /**
     * 获取当前主题
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * 获取视图文件路径
     */
    public function find($name)
    {
        // 如果是主题视图，使用主题命名空间
        if (strpos($name, 'theme::') === 0) {
            $name = substr($name, 7);
            
            if ($this->theme) {
                foreach ($this->themePaths as $path) {
                    $viewPath = $path . '/' . str_replace('.', '/', $name) . '.blade.php';
                    
                    if (file_exists($viewPath)) {
                        return $viewPath;
                    }
                }
            }
        }
        
        // 否则使用默认查找逻辑
        return parent::find($name);
    }

    /**
     * 添加主题视图命名空间
     */
    public function addThemeNamespace($namespace, $hints)
    {
        $this->addNamespace($namespace, $hints);
        return $this;
    }
} 