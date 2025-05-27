<?php

namespace Fastcmf\Modules\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeThemeCommand extends Command
{
    /**
     * 命令名称
     */
    protected $signature = 'make:theme {name : 主题名称} {--admin : 创建后台主题} {--force : 强制覆盖已存在的主题}';

    /**
     * 命令描述
     */
    protected $description = '创建一个新的主题';

    /**
     * 执行命令
     */
    public function handle()
    {
        $name = $this->argument('name');
        $isAdmin = $this->option('admin');
        $force = $this->option('force');
        
        // 确保主题名称格式正确
        $name = Str::kebab($name);
        
        // 如果是后台主题，添加admin_前缀
        if ($isAdmin && !Str::startsWith($name, 'admin_')) {
            $name = 'admin_' . $name;
        }
        
        // 使用绝对路径
        $publicPath = public_path();
        if (empty($publicPath)) {
            $this->error("无法获取public目录路径。");
            return 1;
        }
        
        // 创建themes目录
        $themesPath = $publicPath . '/themes';
        if (!is_dir($themesPath)) {
            try {
                mkdir($themesPath, 0755, true);
                $this->info("已创建主题目录: {$themesPath}");
            } catch (\Exception $e) {
                $this->error("无法创建主题目录: " . $e->getMessage());
                return 1;
            }
        }
        
        // 主题路径
        $path = $themesPath . '/' . $name;
        $this->info("将创建主题到路径: {$path}");
        
        // 检查主题是否已存在
        if (is_dir($path)) {
            if (!$force) {
                $this->error("主题 [$name] 已经存在!");
                $this->info("使用 --force 选项可以覆盖已存在的主题。");
                return 1;
            }
            
            $this->warn("正在覆盖已存在的主题 [$name]...");
            $this->deleteDirectory($path);
        }

        // 创建主题目录结构
        if (!$this->generateThemeStructure($name, $path, $isAdmin)) {
            return 1;
        }
        
        $this->info("主题 [$name] 创建成功!");
        return 0;
    }

    /**
     * 递归删除目录
     */
    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }

    /**
     * 生成主题目录结构
     */
    protected function generateThemeStructure($name, $path, $isAdmin = false)
    {
        try {
            // 创建基本目录
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
                $this->info("创建主题目录: {$path}");
            }
            
            // 创建子目录
            $this->createThemeDirectories($name, $path);
            
            // 创建主题配置文件
            file_put_contents("$path/theme.json", $this->getThemeConfigStub($name));
            
            // 创建默认样式文件
            $cssPath = "$path/assets/css";
            if (!is_dir($cssPath)) {
                mkdir($cssPath, 0755, true);
            }
            file_put_contents("$cssPath/style.css", $this->getStyleStub());
            
            // 创建默认JS文件
            $jsPath = "$path/assets/js";
            if (!is_dir($jsPath)) {
                mkdir($jsPath, 0755, true);
            }
            file_put_contents("$jsPath/app.js", $this->getJsStub());
            
            // 创建默认布局文件
            $layoutsPath = "$path/views/layouts";
            if (!is_dir($layoutsPath)) {
                mkdir($layoutsPath, 0755, true);
            }
            
            if ($isAdmin) {
                // 创建后台布局文件
                file_put_contents("$layoutsPath/admin.blade.php", $this->getAdminLayoutStub($name));
                
                // 创建后台首页视图
                $viewsPath = "$path/views";
                file_put_contents("$viewsPath/index.blade.php", $this->getAdminIndexViewStub());
                
                // 创建后台表单视图
                file_put_contents("$viewsPath/form.blade.php", $this->getAdminFormViewStub());
            } else {
                // 创建前台布局文件
                file_put_contents("$layoutsPath/default.blade.php", $this->getLayoutStub($name));
                
                // 创建默认首页视图
                $viewsPath = "$path/views";
                file_put_contents("$viewsPath/index.blade.php", $this->getIndexViewStub());
            }
            
            return true;
        } catch (\Exception $e) {
            $this->error("创建主题结构失败: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 创建主题目录结构
     */
    protected function createThemeDirectories($name, $path)
    {
        // 创建目录
        $directories = [
            'assets/images',
            'views',
            'views/modules',
            'views/layouts',
            'lang',
            'config',
        ];
        
        foreach ($directories as $directory) {
            $fullPath = "$path/$directory";
            if (!is_dir($fullPath)) {
                try {
                    mkdir($fullPath, 0755, true);
                    $this->line("创建目录: {$fullPath}");
                } catch (\Exception $e) {
                    $this->error("无法创建目录 {$fullPath}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * 获取主题配置模板内容
     */
    protected function getThemeConfigStub($name)
    {
        return json_encode([
            'name' => $name,
            'description' => ucfirst($name) . ' Theme',
            'version' => '1.0.0',
            'author' => 'Your Name',
            'email' => 'your.email@example.com',
        ], JSON_PRETTY_PRINT);
    }
    
    /**
     * 获取样式模板内容
     */
    protected function getStyleStub()
    {
        return <<<CSS
/**
 * 主题样式
 */
body {
    font-family: 'Microsoft YaHei', Arial, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.header {
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 15px 0;
    margin-bottom: 20px;
}

.footer {
    background-color: #333;
    color: #fff;
    padding: 20px 0;
    margin-top: 30px;
    text-align: center;
}

.card {
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    padding: 20px;
}
CSS;
    }
    
    /**
     * 获取JS模板内容
     */
    protected function getJsStub()
    {
        return <<<JS
/**
 * 主题脚本
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Theme loaded!');
});
JS;
    }
    
    /**
     * 获取布局模板内容
     */
    protected function getLayoutStub($name)
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '网站标题')</title>
    <link rel="stylesheet" href="{{ theme()->asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <nav>
                <ul>
                    <li><a href="/">首页</a></li>
                    <li><a href="/about">关于</a></li>
                    <li><a href="/contact">联系我们</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. 保留所有权利。</p>
        </div>
    </footer>

    <script src="{{ theme()->asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
HTML;
    }
    
    /**
     * 获取首页视图模板内容
     */
    protected function getIndexViewStub()
    {
        return <<<HTML
@extends('theme::layouts.default')

@section('title', '首页')

@section('content')
    <div class="card">
        <h2>欢迎使用主题系统</h2>
        <p>这是一个示例页面，您可以根据需要修改它。</p>
    </div>
@endsection
HTML;
    }

    /**
     * 获取后台布局模板内容
     */
    protected function getAdminLayoutStub($name)
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '网站标题')</title>
    <link rel="stylesheet" href="{{ theme()->asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <nav>
                <ul>
                    <li><a href="/">首页</a></li>
                    <li><a href="/about">关于</a></li>
                    <li><a href="/contact">联系我们</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. 保留所有权利。</p>
        </div>
    </footer>

    <script src="{{ theme()->asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>
HTML;
    }

    /**
     * 获取后台首页视图模板内容
     */
    protected function getAdminIndexViewStub()
    {
        return <<<HTML
@extends('theme::layouts.admin')

@section('title', '后台首页')

@section('content')
    <div class="card">
        <h2>欢迎使用后台系统</h2>
        <p>这是一个示例页面，您可以根据需要修改它。</p>
    </div>
@endsection
HTML;
    }

    /**
     * 获取后台表单视图模板内容
     */
    protected function getAdminFormViewStub()
    {
        return <<<HTML
@extends('theme::layouts.admin')

@section('title', '表单示例')

@section('content')
    <div class="card">
        <h2>表单示例</h2>
        <form action="/admin/form" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">名称</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="message">消息</label>
                <textarea id="message" name="message" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">提交</button>
        </form>
    </div>
@endsection
HTML;
    }
} 