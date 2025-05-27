<?php

namespace Fastcmf\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Foundation\Application;

class ModuleManager
{
    /**
     * Laravel 应用实例
     */
    protected $app;

    /**
     * 已加载的模块
     */
    protected $modules = [];

    /**
     * 构造函数
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 发现所有模块
     */
    public function discover()
    {
        $modulesPath = config('modules.path', app_path());

        // 如果模块目录不存在，则直接返回
        if (!File::isDirectory($modulesPath)) {
            return;
        }

        // 获取模块目录
        $moduleDirectories = $this->getModuleDirectories($modulesPath);

        // 触发模块发现前钩子
        Hook::trigger('modules.discover.before', $moduleDirectories);

        foreach ($moduleDirectories as $directory) {
            $name = basename($directory);
            $module = new Module($name, $directory);
            
            // 检查模块是否启用
            if ($module->isEnabled()) {
                $this->modules[$name] = $module;
                
                // 触发模块发现钩子
                Hook::trigger('module.discovered', $module);
            }
        }
        
        // 触发模块发现后钩子
        Hook::trigger('modules.discover.after', $this->modules);
    }

    /**
     * 获取模块目录
     * 
     * @param string $basePath
     * @return array
     */
    protected function getModuleDirectories($basePath)
    {
        // 在app目录下，我们需要查找特定的标记文件来识别模块
        $directories = [];
        
        // 直接使用app目录下的子目录作为模块
        // 但排除一些Laravel默认目录和我们自己的目录
        $excludedDirs = [
            'Http', 'Console', 'Exceptions', 'Providers', 'Events', 
            'Jobs', 'Mail', 'Notifications', 'Policies', 'Rules', 
            'Models', 'Middleware', 'Listeners', 'View', 'Services'
        ];
        
        $allDirs = File::directories($basePath);
        
        foreach ($allDirs as $dir) {
            $dirName = basename($dir);
            
            // 排除Laravel默认目录
            if (in_array($dirName, $excludedDirs)) {
                continue;
            }
            
            // 检查是否有module.json文件，这是模块的标志
            if (File::exists($dir . '/module.json')) {
                $directories[] = $dir;
            }
        }
        
        return $directories;
    }

    /**
     * 启动模块
     */
    public function boot(Module $module)
    {
        try {
            // 触发模块启动前钩子
            Hook::trigger('module.boot.before', $module);
            
            // 注册模块钩子
            $module->registerHooks();
            
            // 注册服务提供者
            $providerPath = $module->getPath() . '/' . config('modules.structure.providers') . 
                          '/' . $module->getName() . 'ServiceProvider.php';
                        
            if (File::exists($providerPath)) {
                $namespace = config('modules.namespace') . '\\' . $module->getName() . 
                           '\\' . config('modules.structure.providers') . '\\' . 
                           $module->getName() . 'ServiceProvider';
                
                if (class_exists($namespace)) {
                    $this->app->register($namespace);
                }
            }
            
            // 触发模块启动后钩子
            Hook::trigger('module.boot.after', $module);
        } catch (\Exception $e) {
            // 记录错误但不中断应用
            \Log::error('模块启动失败: ' . $module->getName() . ' - ' . $e->getMessage());
            
            // 触发模块启动失败钩子
            Hook::trigger('module.boot.failed', [
                'module' => $module,
                'error' => $e
            ]);
        }
    }

    /**
     * 获取所有模块
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * 获取模块
     */
    public function find($name)
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * 检查模块是否存在
     */
    public function has($name)
    {
        return isset($this->modules[$name]);
    }

    /**
     * 启用模块
     */
    public function enable($name)
    {
        if ($this->has($name)) {
            $module = $this->find($name);
            return $module->enable();
        }
        return false;
    }

    /**
     * 禁用模块
     */
    public function disable($name)
    {
        if ($this->has($name)) {
            $module = $this->find($name);
            return $module->disable();
        }
        return false;
    }
    
    /**
     * 安装模块
     */
    public function install($name, $path = null)
    {
        // 触发模块安装前钩子
        Hook::trigger('module.install.before', $name);
        
        // 实际安装逻辑...
        $modulesPath = config('modules.path', app_path());
        $modulePath = $path ?: $modulesPath . '/' . $name;
        
        if (!File::isDirectory($modulePath)) {
            return false;
        }
        
        $module = new Module($name, $modulePath);
        $this->modules[$name] = $module;
        
        // 触发模块安装后钩子
        Hook::trigger('module.install.after', $module);
        
        return $module;
    }
    
    /**
     * 卸载模块
     */
    public function uninstall($name)
    {
        if (!$this->has($name)) {
            return false;
        }
        
        $module = $this->find($name);
        
        // 触发模块卸载前钩子
        Hook::trigger('module.uninstall.before', $module);
        
        // 实际卸载逻辑...
        // 这里可以添加删除文件等操作，但需要谨慎处理
        
        unset($this->modules[$name]);
        
        // 触发模块卸载后钩子
        Hook::trigger('module.uninstall.after', $name);
        
        return true;
    }
} 