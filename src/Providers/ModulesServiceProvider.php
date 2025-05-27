<?php

namespace Fastcmf\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use Fastcmf\Modules\Commands\MakeModuleCommand;
use Fastcmf\Modules\Commands\MakeThemeCommand;
use Fastcmf\Modules\ModuleManager;
use Fastcmf\Modules\Theme;
use Fastcmf\Modules\Hook;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * 启动服务
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/modules.php' => config_path('modules.php'),
            __DIR__ . '/../../config/themes.php' => config_path('themes.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
                MakeThemeCommand::class,
            ]);
        }

        // 注册主题视图命名空间
        $this->registerThemeViewNamespace();

        // 触发模块初始化钩子
        Hook::trigger('modules.init');

        $this->bootModules();
        
        // 触发模块启动完成钩子
        Hook::trigger('modules.boot');
    }

    /**
     * 注册服务
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/modules.php', 'modules'
        );
        
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/themes.php', 'themes'
        );

        // 注册模块管理器
        $this->app->singleton('modules', function ($app) {
            return new ModuleManager($app);
        });
        
        // 注册主题管理器
        $this->app->singleton('theme', function ($app) {
            return new Theme();
        });
        
        // 注册钩子系统
        $this->app->singleton('hook', function ($app) {
            return Hook::getInstance();
        });
        
        // 注册主题服务提供者
        $this->app->register(ThemeServiceProvider::class);
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
            // 触发模块加载前钩子
            Hook::trigger('module.load.before', $module);
            
            $manager->boot($module);
            
            // 触发模块加载后钩子
            Hook::trigger('module.load.after', $module);
        }
    }
    
    /**
     * 注册主题视图命名空间
     */
    protected function registerThemeViewNamespace()
    {
        $theme = $this->app['theme'];
        
        if ($theme->exists()) {
            $this->loadViewsFrom(
                $theme->getThemePath() . '/' . config('themes.structure.views', 'views'),
                config('themes.namespace', 'theme')
            );
        }
    }
} 