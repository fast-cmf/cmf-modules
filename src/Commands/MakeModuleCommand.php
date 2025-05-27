<?php

namespace Fastcmf\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Fastcmf\Modules\Facades\Theme;

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
        $path = config('modules.path', app_path()) . '/' . $name;
        
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
        $modulesPath = config('modules.path', app_path());
        
        // 检查app目录是否存在，不存在则提示错误
        if (!File::isDirectory($modulesPath)) {
            $this->error("模块目录 [$modulesPath] 不存在，请先创建该目录!");
            exit(1);
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
        $this->createModuleDirectories($name, $path);
        
        // 创建路由文件
        $this->createRouteFiles($name, $path);
        
        // 创建服务提供者
        $this->createServiceProvider($name, $path);
        
        // 创建控制器
        $this->createControllers($name, $path);
        
        // 创建模块配置文件
        File::put("$path/module.json", $this->getStub('module', $name));
        
        // 创建钩子文件
        File::put("$path/hooks.php", $this->getStub('hooks', $name));
    }
    
    /**
     * 创建模块目录结构
     */
    protected function createModuleDirectories($name, $path)
    {
        // 在模块目录内创建所有子目录
        $directories = [
            config('modules.structure.controllers'),
            config('modules.structure.providers'),
            config('modules.structure.routes'),
            config('modules.structure.models'),
            config('modules.structure.migrations'),
            config('modules.structure.seeders'),
        ];
        
        foreach ($directories as $directory) {
            // 确保路径是相对于模块目录的
            $fullPath = $path . '/' . $directory;
            if (!File::isDirectory($fullPath)) {
                File::makeDirectory($fullPath, 0755, true);
            }
        }
    }
    
    /**
     * 创建路由文件
     */
    protected function createRouteFiles($name, $path)
    {
        $routesPath = $path . '/' . config('modules.structure.routes');
        if (!File::isDirectory($routesPath)) {
            File::makeDirectory($routesPath, 0755, true);
        }
        
        // 前台路由
        File::put("$routesPath/web.php", $this->getStub('routes/web', $name));
        
        // API路由
        File::put("$routesPath/api.php", $this->getStub('routes/api', $name));
        
        // 后台路由
        File::put("$routesPath/admin.php", $this->getStub('routes/admin', $name));
    }
    
    /**
     * 创建服务提供者
     */
    protected function createServiceProvider($name, $path)
    {
        $providersPath = $path . '/' . config('modules.structure.providers');
        if (!File::isDirectory($providersPath)) {
            File::makeDirectory($providersPath, 0755, true);
        }
        File::put(
            "$providersPath/{$name}ServiceProvider.php", 
            $this->getStub('provider', $name)
        );
    }
    
    /**
     * 创建控制器
     */
    protected function createControllers($name, $path)
    {
        // 控制器目录
        $controllerPath = $path . '/' . config('modules.structure.controllers');
        if (!File::isDirectory($controllerPath)) {
            File::makeDirectory($controllerPath, 0755, true);
        }
        
        // 前台控制器
        File::put(
            "$controllerPath/{$name}Controller.php", 
            $this->getStub('controllers/frontend', $name)
        );
        
        // 后台控制器
        File::put(
            "$controllerPath/Admin{$name}Controller.php", 
            $this->getStub('controllers/backend', $name)
        );
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
            case 'controllers/frontend':
                return $this->getDefaultFrontendControllerStub($name);
            case 'controllers/backend':
                return $this->getDefaultBackendControllerStub($name);
            case 'routes/web':
                return $this->getDefaultWebRouteStub($name);
            case 'routes/api':
                return $this->getDefaultApiRouteStub($name);
            case 'routes/admin':
                return $this->getDefaultAdminRouteStub($name);
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
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$name} 模块</title>
    <style>
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f5f5f5;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .card-body {
            padding: 15px;
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">{$name} 模块</div>
            <div class="card-body">
                <h1>{$name} 模块</h1>
                <p>这是 {$name} 模块的首页。</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    protected function getDefaultProviderStub($name)
    {
        return <<<PHP
<?php

namespace App\\{$name}\\Providers;

use Illuminate\\Support\\ServiceProvider;
use Fastcmf\\Modules\\Facades\\Theme;

class {$name}ServiceProvider extends ServiceProvider
{
    /**
     * 启动服务
     */
    public function boot()
    {
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        
        // 从主题目录加载视图
        \$themePath = config('themes.path') . '/' . Theme::current()->getName();
        \$viewsPath = \$themePath . '/' . config('themes.structure.views') . '/modules/{$name}';
        
        if (is_dir(\$viewsPath)) {
            \$this->loadViewsFrom(\$viewsPath, '{$name}');
        }
        
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
    
    protected function getDefaultFrontendControllerStub($name)
    {
        return <<<PHP
<?php

namespace App\\{$name}\\Http\\Controllers;

use Fastcmf\\Modules\\Http\\Controllers\\HomeBaseController;

class {$name}Controller extends HomeBaseController
{
    /**
     * 显示主页
     */
    public function index()
    {
        return \$this->view('{$name}::{$name}.index');
    }
    
    /**
     * 显示详情页
     */
    public function show(\$id)
    {
        return \$this->view('{$name}::{$name}.show', ['id' => \$id]);
    }
}
PHP;
    }
    
    protected function getDefaultBackendControllerStub($name)
    {
        return <<<PHP
<?php

namespace App\\{$name}\\Http\\Controllers;

use Fastcmf\\Modules\\Http\\Controllers\\AdminBaseController;

class Admin{$name}Controller extends AdminBaseController
{
    /**
     * 显示列表页
     */
    public function index()
    {
        return \$this->view('{$name}::admin.{$name}.index');
    }
    
    /**
     * 显示创建页
     */
    public function create()
    {
        return \$this->view('{$name}::admin.{$name}.create');
    }
    
    /**
     * 保存数据
     */
    public function store()
    {
        // 处理保存逻辑
        return \$this->adminSuccess('{$name}添加成功');
    }
    
    /**
     * 显示编辑页
     */
    public function edit(\$id)
    {
        return \$this->view('{$name}::admin.{$name}.edit', ['id' => \$id]);
    }
    
    /**
     * 更新数据
     */
    public function update(\$id)
    {
        // 处理更新逻辑
        return \$this->adminSuccess('{$name}更新成功');
    }
    
    /**
     * 删除数据
     */
    public function destroy(\$id)
    {
        // 处理删除逻辑
        return \$this->adminSuccess('{$name}删除成功');
    }
}
PHP;
    }
    
    protected function getDefaultWebRouteStub($name)
    {
        $routeName = Str::lower($name);
        
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use App\\{$name}\\Http\\Controllers\\{$name}Controller;

Route::prefix('{$routeName}')->name('{$routeName}.')->group(function () {
    Route::get('/', [{$name}Controller::class, 'index'])->name('index');
    Route::get('/{id}', [{$name}Controller::class, 'show'])->name('show');
});
PHP;
    }
    
    protected function getDefaultApiRouteStub($name)
    {
        $routeName = Str::lower($name);
        
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;

Route::prefix('api/{$routeName}')->name('api.{$routeName}.')->group(function () {
    // API路由定义
});
PHP;
    }
    
    protected function getDefaultAdminRouteStub($name)
    {
        $routeName = Str::lower($name);
        
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use App\\{$name}\\Http\\Controllers\\Admin{$name}Controller;

Route::prefix('admin/{$routeName}')->name('admin.{$routeName}.')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', [Admin{$name}Controller::class, 'index'])->name('index');
    Route::get('/create', [Admin{$name}Controller::class, 'create'])->name('create');
    Route::post('/', [Admin{$name}Controller::class, 'store'])->name('store');
    Route::get('/{id}/edit', [Admin{$name}Controller::class, 'edit'])->name('edit');
    Route::put('/{id}', [Admin{$name}Controller::class, 'update'])->name('update');
    Route::delete('/{id}', [Admin{$name}Controller::class, 'destroy'])->name('destroy');
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