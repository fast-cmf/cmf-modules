<?php

namespace Fastcmf\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class Theme
{
    /**
     * 主题名称
     */
    protected $name;

    /**
     * 主题路径
     */
    protected $path;

    /**
     * 构造函数
     */
    public function __construct($name = null)
    {
        $this->name = $name ?: $this->getDefaultTheme();
        $this->path = $this->getThemePath($this->name);
    }

    /**
     * 获取默认主题
     */
    public function getDefaultTheme()
    {
        return Config::get('themes.default', 'default');
    }

    /**
     * 获取当前主题
     */
    public static function current()
    {
        $themeName = session('current_theme', Config::get('themes.default', 'default'));
        return new static($themeName);
    }

    /**
     * 获取主题路径
     */
    public function getThemePath($theme = null)
    {
        $theme = $theme ?: $this->name;
        return Config::get('themes.path', public_path('themes')) . '/' . $theme;
    }

    /**
     * 获取主题名称
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取主题资源URL
     */
    public function asset($path)
    {
        $themesUrl = Config::get('themes.url', '/themes');
        return $themesUrl . '/' . $this->name . '/' . Config::get('themes.structure.assets', 'assets') . '/' . $path;
    }

    /**
     * 获取主题视图路径
     */
    public function getViewPath($view)
    {
        return $this->path . '/' . config('themes.structure.views', 'views') . '/' . str_replace('.', '/', $view) . '.blade.php';
    }

    /**
     * 检查主题是否存在
     */
    public function exists()
    {
        return File::isDirectory($this->path);
    }

    /**
     * 获取所有主题
     */
    public static function all()
    {
        $themesPath = Config::get('themes.path', resource_path('themes'));
        
        if (!File::isDirectory($themesPath)) {
            return [];
        }
        
        $directories = File::directories($themesPath);
        $themes = [];
        
        foreach ($directories as $directory) {
            $name = basename($directory);
            $themes[$name] = new static($name);
        }
        
        return $themes;
    }

    /**
     * 切换主题
     */
    public static function set($name)
    {
        $theme = new static($name);
        
        if (!$theme->exists()) {
            return false;
        }
        
        session(['current_theme' => $name]);
        return true;
    }

    /**
     * 获取主题配置
     */
    public function getConfig($key = null, $default = null)
    {
        $configPath = $this->path . '/' . config('themes.structure.config', 'config') . '/theme.json';
        
        if (!File::exists($configPath)) {
            return $default;
        }
        
        $config = json_decode(File::get($configPath), true);
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
} 