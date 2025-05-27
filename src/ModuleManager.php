<?php
// +----------------------------------------------------------------------
// | ModuleManager.php模块业务逻辑
// +----------------------------------------------------------------------
// | Author: LuYuan 758899293@qq.com
// +----------------------------------------------------------------------

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
        $modulesPath = config('modules.path', app_path('Modules'));

        if (!File::isDirectory($modulesPath)) {
            return;
        }

        $directories = File::directories($modulesPath);

        foreach ($directories as $directory) {
            $name = basename($directory);
            $this->modules[$name] = new Module($name, $directory);
        }
    }

    /**
     * 启动模块
     */
    public function boot(Module $module)
    {
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
}