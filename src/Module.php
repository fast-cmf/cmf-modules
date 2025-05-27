<?php

namespace Fastcmf\Modules;

use Illuminate\Support\Facades\File;

class Module
{
    /**
     * 模块名称
     */
    protected $name;

    /**
     * 模块路径
     */
    protected $path;

    /**
     * 构造函数
     */
    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * 获取模块名称
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取模块路径
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 检查模块依赖
     */
    public function checkDependencies()
    {
        $configPath = $this->getPath() . '/module.json';
        if (File::exists($configPath)) {
            $config = json_decode(File::get($configPath), true);
            
            if (isset($config['dependencies']) && is_array($config['dependencies'])) {
                foreach ($config['dependencies'] as $dependency) {
                    if (!app('modules')->has($dependency)) {
                        throw new \Exception("Module {$this->name} requires {$dependency} module.");
                    }
                }
            }
        }
        
        return true;
    }

    /**
     * 获取模块是否启用
     */
    public function isEnabled()
    {
        $configPath = $this->getPath() . '/module.json';
        if (File::exists($configPath)) {
            $config = json_decode(File::get($configPath), true);
            return isset($config['enabled']) ? (bool) $config['enabled'] : true;
        }
        
        return true;
    }
    
    /**
     * 获取模块配置
     */
    public function getConfig($key = null, $default = null)
    {
        $configPath = $this->getPath() . '/module.json';
        if (!File::exists($configPath)) {
            return $default;
        }
        
        $config = json_decode(File::get($configPath), true);
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? $default;
    }
    
    /**
     * 注册钩子
     */
    public function registerHooks()
    {
        $hooksPath = $this->getPath() . '/hooks.php';
        
        if (File::exists($hooksPath)) {
            require_once $hooksPath;
        }
        
        // 触发模块钩子注册事件
        Hook::trigger('module.hooks.registered', $this);
        
        return $this;
    }
    
    /**
     * 启用模块
     */
    public function enable()
    {
        $configPath = $this->getPath() . '/module.json';
        if (File::exists($configPath)) {
            $config = json_decode(File::get($configPath), true);
            $config['enabled'] = true;
            
            File::put($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // 触发模块启用钩子
            Hook::trigger('module.enabled', $this);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 禁用模块
     */
    public function disable()
    {
        $configPath = $this->getPath() . '/module.json';
        if (File::exists($configPath)) {
            $config = json_decode(File::get($configPath), true);
            $config['enabled'] = false;
            
            File::put($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // 触发模块禁用钩子
            Hook::trigger('module.disabled', $this);
            
            return true;
        }
        
        return false;
    }
} 