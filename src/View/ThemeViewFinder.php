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
     * 原始视图路径
     */
    protected $originalPaths;

    /**
     * 设置当前主题
     */
    public function setTheme(Theme $theme)
    {
        $this->theme = $theme;
        $this->originalPaths = $this->paths;
        
        // 如果主题存在，添加主题视图路径
        if ($theme->exists()) {
            $themePath = $theme->getThemePath();
            
            if (is_dir($themePath)) {
                // 将主题视图路径添加到视图路径的最前面
                array_unshift($this->paths, $themePath);
                
                // 清除视图缓存
                $this->flush();
            }
        }
    }

    /**
     * 获取当前主题
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * 查找视图文件
     */
    public function find($name)
    {
        // 如果是主题视图格式 theme::view
        if (strpos($name, 'theme::') === 0) {
            $name = substr($name, 7);
            
            // 只在主题目录中查找
            if ($this->theme && $this->theme->exists()) {
                $themePath = $this->theme->getThemePath();
                
                // 如果是绝对路径（以/开头）
                if (strpos($name, '/') === 0) {
                    $path = $themePath . $name . '.blade.php';
                } else {
                    $path = $themePath . '/' . str_replace('.', '/', $name) . '.blade.php';
                }
                
                if (file_exists($path)) {
                    return $path;
                }
                
                throw new \InvalidArgumentException("View [{$name}] not found in theme [{$this->theme->getName()}].");
            }
        }
        
        // 使用默认的视图查找逻辑
        return parent::find($name);
    }

    /**
     * 重置视图路径
     */
    public function resetPaths()
    {
        if ($this->originalPaths) {
            $this->paths = $this->originalPaths;
            $this->flush();
        }
    }
} 