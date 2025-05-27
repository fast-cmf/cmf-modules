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
    protected $signature = 'make:theme {name : 主题名称}';

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
        $name = Str::kebab($name);
        
        $themesPath = config('themes.path', resource_path('themes'));
        $themePath = $themesPath . '/' . $name;
        
        if (File::isDirectory($themePath)) {
            $this->error("主题 [{$name}] 已存在！");
            return 1;
        }
        
        $this->createDirectories($themePath);
        $this->createFiles($name, $themePath);
        
        $this->info("主题 [{$name}] 创建成功！");
        return 0;
    }

    /**
     * 创建主题目录结构
     */
    protected function createDirectories($themePath)
    {
        $structure = config('themes.structure', [
            'views' => 'views',
            'assets' => 'assets',
            'lang' => 'lang',
            'config' => 'config',
        ]);
        
        // 创建主题根目录
        File::makeDirectory($themePath, 0755, true);
        
        // 创建子目录
        foreach ($structure as $directory) {
            File::makeDirectory($themePath . '/' . $directory, 0755, true);
        }
        
        // 创建资源子目录
        $assetDirs = ['css', 'js', 'images'];
        foreach ($assetDirs as $dir) {
            File::makeDirectory($themePath . '/' . $structure['assets'] . '/' . $dir, 0755, true);
        }
        
        // 创建布局目录
        File::makeDirectory($themePath . '/' . $structure['views'] . '/layouts', 0755, true);
    }

    /**
     * 创建主题文件
     */
    protected function createFiles($name, $themePath)
    {
        $structure = config('themes.structure', [
            'views' => 'views',
            'assets' => 'assets',
            'config' => 'config',
        ]);
        
        // 创建主题配置文件
        $themeConfig = [
            'name' => $name,
            'description' => '这是一个新的主题',
            'author' => '作者名称',
            'version' => '1.0.0',
        ];
        
        File::put(
            $themePath . '/theme.json',
            json_encode($themeConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        // 创建布局文件
        $layoutContent = <<<EOT
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \$title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ theme()->asset('css/style.css') }}">
    @stack('styles')
</head>
<body>
    <header>
        <h1>{{ config('app.name') }}</h1>
    </header>
    
    <main>
        @yield('content')
    </main>
    
    <footer>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </footer>
    
    <script src="{{ theme()->asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
EOT;
        
        File::put(
            $themePath . '/' . $structure['views'] . '/layouts/default.blade.php',
            $layoutContent
        );
        
        // 创建首页视图
        $homeContent = <<<EOT
@extends('theme::layouts.default')

@section('content')
    <h2>欢迎使用 {{ theme()->getName() }} 主题</h2>
    <p>这是一个新创建的主题。</p>
@endsection
EOT;
        
        File::put(
            $themePath . '/' . $structure['views'] . '/index.blade.php',
            $homeContent
        );
        
        // 创建CSS文件
        $cssContent = <<<EOT
/* 
 * 主题: {$name}
 * 版本: 1.0.0
 */
 
body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    color: #333;
}

header, footer {
    background: #f4f4f4;
    padding: 20px;
    text-align: center;
}

main {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 20px;
}

h1, h2, h3 {
    color: #333;
}

a {
    color: #0066cc;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
EOT;
        
        File::put(
            $themePath . '/' . $structure['assets'] . '/css/style.css',
            $cssContent
        );
        
        // 创建JS文件
        $jsContent = <<<EOT
/**
 * 主题: {$name}
 * 版本: 1.0.0
 */
 
(function() {
    'use strict';
    
    // 主题初始化
    console.log('主题 {$name} 已加载');
})();
EOT;
        
        File::put(
            $themePath . '/' . $structure['assets'] . '/js/app.js',
            $jsContent
        );
    }
} 