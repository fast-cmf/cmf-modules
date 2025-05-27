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
    protected $signature = 'make:theme {name : 主题名称} {--admin : 创建后台主题}';

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
        
        // 确保主题名称格式正确
        $name = Str::kebab($name);
        
        // 如果是后台主题，添加admin_前缀
        if ($isAdmin && !Str::startsWith($name, 'admin_')) {
            $name = 'admin_' . $name;
        }
        
        // 确保基础目录结构存在
        $this->ensureThemesDirectoryExists();
        
        // 主题路径
        $path = config('themes.path', public_path('themes')) . '/' . $name;
        
        // 检查主题是否已存在
        if (File::exists($path)) {
            $this->error("主题 [$name] 已经存在!");
            return;
        }

        // 创建主题目录结构
        $this->generateThemeStructure($name, $path, $isAdmin);
        
        $this->info("主题 [$name] 创建成功!");
    }

    /**
     * 确保主题目录存在
     */
    protected function ensureThemesDirectoryExists()
    {
        $themesPath = config('themes.path', public_path('themes'));
        
        // 确保themes目录存在
        if (!File::isDirectory($themesPath)) {
            File::makeDirectory($themesPath, 0755, true);
        }
    }

    /**
     * 生成主题目录结构
     */
    protected function generateThemeStructure($name, $path, $isAdmin = false)
    {
        // 创建基本目录
        File::makeDirectory($path, 0755, true);
        
        // 创建子目录
        $this->createThemeDirectories($name, $path);
        
        // 创建主题配置文件
        File::put("$path/theme.json", $this->getThemeConfigStub($name));
        
        // 创建默认样式文件
        $cssPath = "$path/" . config('themes.structure.assets', 'assets') . "/css";
        File::makeDirectory($cssPath, 0755, true);
        File::put("$cssPath/style.css", $this->getStyleStub());
        
        // 创建默认JS文件
        $jsPath = "$path/" . config('themes.structure.assets', 'assets') . "/js";
        File::makeDirectory($jsPath, 0755, true);
        File::put("$jsPath/app.js", $this->getJsStub());
        
        // 创建默认布局文件
        $layoutsPath = "$path/" . config('themes.structure.views', 'views') . "/layouts";
        File::makeDirectory($layoutsPath, 0755, true);
        
        if ($isAdmin) {
            // 创建后台布局文件
            File::put("$layoutsPath/admin.blade.php", $this->getAdminLayoutStub($name));
            
            // 创建后台首页视图
            $viewsPath = "$path/" . config('themes.structure.views', 'views');
            File::put("$viewsPath/index.blade.php", $this->getAdminIndexViewStub());
            
            // 创建后台表单视图
            File::put("$viewsPath/form.blade.php", $this->getAdminFormViewStub());
        } else {
            // 创建前台布局文件
            File::put("$layoutsPath/default.blade.php", $this->getLayoutStub($name));
            
            // 创建默认首页视图
            $viewsPath = "$path/" . config('themes.structure.views', 'views');
            File::put("$viewsPath/index.blade.php", $this->getIndexViewStub());
        }
    }
    
    /**
     * 创建主题目录结构
     */
    protected function createThemeDirectories($name, $path)
    {
        // 创建目录
        $directories = [
            config('themes.structure.assets', 'assets') . '/images',
            config('themes.structure.views', 'views'),
            config('themes.structure.views', 'views') . '/modules',
            config('themes.structure.views', 'views') . '/layouts',
            config('themes.structure.lang', 'lang'),
            config('themes.structure.config', 'config'),
        ];
        
        foreach ($directories as $directory) {
            File::makeDirectory("$path/$directory", 0755, true);
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