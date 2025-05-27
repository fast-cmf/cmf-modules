<?php
// +----------------------------------------------------------------------
// | MakeModuleCommand.php模块业务逻辑
// +----------------------------------------------------------------------
// | Author: LuYuan 758899293@qq.com
// +----------------------------------------------------------------------
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
    }

    /**
     * 获取模板内容
     */
    protected function getStub($type, $name)
    {
        $stub = File::get(__DIR__ . "/../../stubs/$type.stub");

        return str_replace(
            ['{{ModuleName}}', '{{moduleName}}', '{{module_name}}'],
            [$name, Str::camel($name), Str::snake($name)],
            $stub
        );
    }

}