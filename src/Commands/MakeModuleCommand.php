<?php

namespace Fastcmf\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'make:module {name : 模块名称}';

    /**
     * 命令描述
     */
    protected $description = '创建一个新的模块';

    /**
     * 执行命令
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        // 确保模块名称格式正确
        $name = Str::studly($name);
        
        // 确保基础目录结构存在
        $this->ensureModulesDirectoryExists();
        
        // 模块路径
        $path = config('modules.path', app_path('Modules')) . '/' . $name;
        
        // 检查模块是否已存在
        if (File::exists($path)) {
            $this->error("模块 [$name] 已经存在!");
            return;
        }

        // 创建模块目录结构
        $this->generateModuleStructure($name, $path);
        
        $this->info("模块 [$name] 创建成功!");
    }

    /**
     * 确保模块目录存在
     */
    protected function ensureModulesDirectoryExists()
    {
        $modulesPath = config('modules.path', app_path('Modules'));
        
        // 确保app目录存在
        $appPath = app_path();
        if (!File::isDirectory($appPath)) {
            File::makeDirectory($appPath, 0755, true);
        }
        
        // 确保Modules目录存在
        if (!File::isDirectory($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }
    }

    /**
     * 生成模块目录结构
     */
    protected function generateModuleStructure($name, $path)
    {
        // 创建基本目录
        File::makeDirectory($path, 0755, true);
        
        // 创建子目录
        foreach (config('modules.structure') as $directory) {
            File::makeDirectory("$path/$directory", 0755, true);
        }
        
        // 创建路由文件
        $routesPath = "$path/" . config('modules.structure.routes');
        File::put("$routesPath/web.php", $this->getStub('routes/web', $name));
        File::put("$routesPath/api.php", $this->getStub('routes/api', $name));
        
        // 创建服务提供者
        $providersPath = "$path/" . config('modules.structure.providers');
        File::put(
            "$providersPath/{$name}ServiceProvider.php", 
            $this->getStub('provider', $name)
        );
        
        // 创建控制器
        $controllersPath = "$path/" . config('modules.structure.controllers');
        File::put(
            "$controllersPath/{$name}Controller.php", 
            $this->getStub('controller', $name)
        );
        
        // 创建模块配置文件
        File::put("$path/module.json", $this->getStub('module', $name));
        
        // 创建钩子文件
        File::put("$path/hooks.php", $this->getStub('hooks', $name));
        
        // 创建默认视图文件
        $viewsPath = "$path/" . config('modules.structure.views');
        if (!File::exists("$viewsPath/index.blade.php")) {
            File::put("$viewsPath/index.blade.php", $this->getDefaultViewStub($name));
        }
    }

    /**
     * 获取模板内容
     */
    protected function getStub($type, $name)
    {
        $stubPath = __DIR__ . "/../../stubs/$type.stub";
        
        if (!File::exists($stubPath)) {
            return $this->getDefaultStub($type, $name);
        }
        
        $stub = File::get($stubPath);
        
        return str_replace(
            ['{{ModuleName}}', '{{moduleName}}', '{{module_name}}'],
            [$name, Str::camel($name), Str::snake($name)],
            $stub
        );
    }
    
    /**
     * 获取默认模板内容
     */
    protected function getDefaultStub($type, $name)
    {
        switch ($type) {
            case 'provider':
                return $this->getDefaultProviderStub($name);
            case 'controller':
                return $this->getDefaultControllerStub($name);
            case 'routes/web':
                return $this->getDefaultWebRouteStub($name);
            case 'routes/api':
                return $this->getDefaultApiRouteStub($name);
            case 'module':
                return $this->getDefaultModuleStub($name);
            case 'hooks':
                return $this->getDefaultHooksStub($name);
            default:
                return '';
        }
    }
    
    /**
     * 获取默认视图模板内容
     */
    protected function getDefaultViewStub($name)
    {
        return <<<HTML
<div>
    <h1>{$name} 模块</h1>
    <p>这是 {$name} 模块的首页。</p>
</div>
HTML;
    }
    
    protected function getDefaultProviderStub($name)
    {
        return <<<PHP
<?php

namespace App\\Modules\\{$name}\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    /**
     * 启动服务
     */
    public function boot()
    {
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        \$this->loadViewsFrom(__DIR__ . '/../Views', '{$name}');
        
        // 加载迁移文件
        \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * 注册服务
     */
    public function register()
    {
        //
    }
}
PHP;
    }
    
    protected function getDefaultControllerStub($name)
    {
        return <<<PHP
<?php

namespace App\\Modules\\{$name}\\Controllers;

use App\\Http\\Controllers\\Controller;

class {$name}Controller extends Controller
{
    /**
     * 显示主页
     */
    public function index()
    {
        return view('{$name}::index');
    }
}
PHP;
    }
    
    protected function getDefaultWebRouteStub($name)
    {
        $controllerName = $name . 'Controller';
        $routeName = Str::lower($name);
        
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use App\\Modules\\{$name}\\Controllers\\{$controllerName};

Route::prefix('{$routeName}')->group(function () {
    Route::get('/', [{$controllerName}::class, 'index']);
});
PHP;
    }
    
    protected function getDefaultApiRouteStub($name)
    {
        $routeName = Str::lower($name);
        
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;

Route::prefix('api/{$routeName}')->group(function () {
    // API路由定义
});
PHP;
    }
    
    protected function getDefaultModuleStub($name)
    {
        return json_encode([
            'name' => $name,
            'description' => "{$name} module",
            'enabled' => true,
            'version' => '1.0.0',
            'dependencies' => []
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * 获取默认钩子模板内容
     */
    protected function getDefaultHooksStub($name)
    {
        return <<<PHP
<?php

/**
 * {$name} 模块钩子
 */
 
use Fastcmf\\Modules\\Facades\\Hook;

/**
 * 注册模块钩子
 * 
 * 示例:
 * 
 * Hook::listen('user.registered', function(\$user) {
 *     // 处理用户注册事件
 *     return \$user;
 * });
 * 
 * Hook::listen('post.content', function(\$content) {
 *     // 过滤内容
 *     return \$content;
 * });
 */

// 模块启动时
Hook::listen('module.boot.after', function(\$module) {
    if (\$module->getName() === '{$name}') {
        // 模块启动后的操作
    }
    return \$module;
});
PHP;
    }
} 