<?php

namespace Fastcmf\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;
use Fastcmf\Modules\View\ThemeViewFinder;
use Fastcmf\Modules\Theme;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * 启动服务
     */
    public function boot()
    {
        // 发布资源
        $this->publishAssets();
        
        // 注册视图组件
        $this->registerViewFinder();
        
        // 注册中间件
        $this->registerMiddleware();
        
        // 注册Blade指令
        $this->registerBladeDirectives();
    }

    /**
     * 注册服务
     */
    public function register()
    {
        // 注册主题实例
        $this->app->singleton('theme', function ($app) {
            return Theme::current();
        });
        
        // 合并配置
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/themes.php', 'themes'
        );
    }

    /**
     * 注册视图查找器
     */
    protected function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            $paths = $app['config']['view.paths'];
            $themeViewFinder = new ThemeViewFinder($app['files'], $paths);
            
            // 设置当前主题
            $theme = Theme::current();
            $themeViewFinder->setTheme($theme);
            
            return $themeViewFinder;
        });
    }

    /**
     * 发布主题资源
     */
    protected function publishAssets()
    {
        $themesPath = config('themes.path', public_path('themes'));
        
        if (!is_dir($themesPath)) {
            return;
        }
        
        $themes = Theme::all();
        
        foreach ($themes as $theme) {
            $assetsPath = $theme->getThemePath() . '/' . config('themes.structure.assets', 'assets');
            
            if (is_dir($assetsPath)) {
                $this->publishes([
                    $assetsPath => public_path('themes/' . $theme->getName()),
                ], 'themes');
            }
        }
    }

    /**
     * 注册中间件
     */
    protected function registerMiddleware()
    {
        $router = $this->app['router'];
        
        // 注册主题中间件
        $router->aliasMiddleware('theme', \Fastcmf\Modules\Middleware\ThemeMiddleware::class);
    }
    
    /**
     * 注册Blade指令
     */
    protected function registerBladeDirectives()
    {
        \Blade::directive('theme', function ($expression) {
            return "<?php echo app('theme')->asset($expression); ?>";
        });
    }
} 