<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ModuleManager;
use App\Services\Theme;
use App\Console\Commands;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 合并配置
        $this->mergeConfigFrom(__DIR__ . '/../config/modules.php', 'modules');
        $this->mergeConfigFrom(__DIR__ . '/../config/themes.php', 'themes');

        // 注册模块管理器
        $this->app->singleton('modules', function ($app) {
            return new ModuleManager($app);
        });

        // 注册主题
        $this->app->singleton('theme', function ($app) {
            return new Theme();
        });

        // 注册命令
        $this->commands([
            Commands\MakeModuleCommand::class,
            Commands\MakeThemeCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(ModuleManager $moduleManager)
    {
        //
    }
} 