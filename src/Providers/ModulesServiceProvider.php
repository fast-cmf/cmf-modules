<?php
// +----------------------------------------------------------------------
// | ModulesServiceProvider.php模块业务逻辑
// +----------------------------------------------------------------------
// | Author: LuYuan 758899293@qq.com
// +----------------------------------------------------------------------
namespace Fastcmf\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use Fastcmf\Modules\Commands\MakeModuleCommand;
use Fastcmf\Modules\ModuleManager;
use Fastcmf\Modules\Theme;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * 启动服务
     */
    public function boot(Module $module)
    {
        $this->publishes([
            __DIR__ . '/../../config/modules.php' => config_path('modules.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
            ]);
        }

        $this->bootModules();

        // 发布模块资源
        if ($this->app->runningInConsole()) {
            $assetsPath = $module->getPath() . '/Resources/assets';
            if (File::isDirectory($assetsPath)) {
                $this->publishes([
                    $assetsPath => public_path('modules/' . strtolower($module->getName())),
                ], 'modules-assets');
            }
        }
    }

    /**
     * 注册服务
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/modules.php', 'modules'
        );

        $this->app->singleton('modules', function ($app) {
            return new ModuleManager($app);
        });

        $this->app->singleton('theme', function ($app) {
            return new Theme();
        });
    }

    /**
     * 启动所有模块
     */
    protected function bootModules()
    {
        $manager = $this->app['modules'];

        if (config('modules.auto_discover', true)) {
            $manager->discover();
        }

        foreach ($manager->getModules() as $module) {
            $manager->boot($module);
        }
    }

}