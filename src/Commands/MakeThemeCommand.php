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
        
        // 使用配置中的主题路径
        $themesPath = config('themes.path', public_path('themes'));
        
        // 确保主题目录存在
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
        
        // 检查主题是否已存在
        if (is_dir($path)) {
            if (!$force) {
                $this->error("主题 [$name] 已经存在!");
                $this->info("使用 --force 选项可以覆盖已存在的主题。");
                return 1;
            }
            
            $this->warn("正在覆盖已存在的主题 [$name]...");
            File::deleteDirectory($path);
        }

        // 创建主题目录结构
        if (!$this->generateThemeStructure($name, $path, $isAdmin)) {
            return 1;
        }
        
        $this->info("主题 [$name] 创建成功!");
        return 0;
    }

    /**
     * 生成主题目录结构
     */
    protected function generateThemeStructure($name, $path, $isAdmin = false)
    {
        try {
            // 创建基本目录
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true);
            }
            
            // 创建子目录
            $this->createThemeDirectories($name, $path);
            
            // 创建主题配置文件
            File::put("$path/theme.json", $this->getThemeConfigStub($name));
            
            // 创建默认样式文件
            $cssPath = "$path/assets/css";
            if (!File::isDirectory($cssPath)) {
                File::makeDirectory($cssPath, 0755, true);
            }
            File::put("$cssPath/style.css", $this->getStyleStub());
            
            // 创建默认JS文件
            $jsPath = "$path/assets/js";
            if (!File::isDirectory($jsPath)) {
                File::makeDirectory($jsPath, 0755, true);
            }
            File::put("$jsPath/app.js", $this->getJsStub());
            
            if ($isAdmin) {
                // 后台主题
                
                // 创建公共目录下的布局文件
                $publicPath = "$path/public";
                File::put("$publicPath/base.blade.php", $this->getAdminLayoutStub($name));
                
                // 创建blog应用的admin_index控制器视图
                $blogPath = "$path/blog/admin_index";
                File::put("$blogPath/index.blade.php", $this->getAdminBlogIndexStub());
                File::put("$blogPath/create.blade.php", $this->getAdminBlogCreateStub());
                
            } else {
                // 前台主题
                
                // 创建blog应用的public目录下的布局文件
                $blogPublicPath = "$path/blog/public";
                File::put("$blogPublicPath/base.blade.php", $this->getLayoutStub($name));
                
                // 创建blog应用的index控制器视图
                $blogIndexPath = "$path/blog/index";
                File::put("$blogIndexPath/index.blade.php", $this->getBlogIndexStub());
                File::put("$blogIndexPath/detail.blade.php", $this->getBlogDetailStub());
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
        // 创建基础目录
        $directories = [
            'assets/images',
            'assets/css',
            'assets/js',
        ];
        
        // 根据是否为后台主题创建不同的目录结构
        if (strpos($name, 'admin_') === 0) {
            // 后台主题目录结构
            $directories = array_merge($directories, [
                'public',
                'public/assets',
                'blog',
                'blog/admin_index',
            ]);
        } else {
            // 前台主题目录结构
            $directories = array_merge($directories, [
                'blog',
                'blog/public',
                'blog/public/assets',
                'blog/index',
            ]);
        }
        
        foreach ($directories as $directory) {
            $fullPath = "$path/$directory";
            if (!File::isDirectory($fullPath)) {
                try {
                    File::makeDirectory($fullPath, 0755, true);
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
                    <li><a href="/blog">博客</a></li>
                    <li><a href="/about">关于</a></li>
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
    <title>@yield('title', '后台管理系统')</title>
    <link rel="stylesheet" href="{{ theme()->asset('css/style.css') }}">
    @yield('styles')
</head>
<body class="admin-panel">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>{{ config('app.name', 'Laravel') }}</h2>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin">仪表盘</a></li>
                    <li><a href="/admin/blog">博客管理</a></li>
                    <li><a href="/admin/settings">系统设置</a></li>
                </ul>
            </nav>
        </aside>
        
        <div class="admin-content">
            <header class="admin-header">
                <div class="admin-header-left">
                    <button class="toggle-sidebar">≡</button>
                </div>
                <div class="admin-header-right">
                    <div class="admin-user-menu">
                        <span>管理员</span>
                        <a href="/admin/logout">退出</a>
                    </div>
                </div>
            </header>
            
            <main class="admin-main">
                @yield('content')
            </main>
            
            <footer class="admin-footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. 保留所有权利。</p>
            </footer>
        </div>
    </div>

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

    /**
     * 获取头部视图模板内容
     */
    protected function getHeaderStub()
    {
        return <<<HTML
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
HTML;
    }

    /**
     * 获取底部视图模板内容
     */
    protected function getFooterStub()
    {
        return <<<HTML
<footer class="footer">
    <div class="container">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. 保留所有权利。</p>
    </div>
</footer>
HTML;
    }

    /**
     * 获取blog模块的示例视图内容
     */
    protected function getBlogIndexStub()
    {
        return <<<HTML
@extends('blog.public.base')

@section('title', '博客首页')

@section('content')
    <div class="card">
        <h2>博客首页</h2>
        <p>这是一个示例页面，您可以根据需要修改它。</p>
        
        <div class="blog-list">
            <div class="blog-item">
                <h3><a href="/blog/detail/1">第一篇博客文章</a></h3>
                <div class="blog-meta">
                    <span>发布时间: 2023-05-20</span>
                    <span>作者: Admin</span>
                </div>
                <div class="blog-summary">
                    这是第一篇博客文章的摘要内容...
                </div>
            </div>
            
            <div class="blog-item">
                <h3><a href="/blog/detail/2">第二篇博客文章</a></h3>
                <div class="blog-meta">
                    <span>发布时间: 2023-05-21</span>
                    <span>作者: Admin</span>
                </div>
                <div class="blog-summary">
                    这是第二篇博客文章的摘要内容...
                </div>
            </div>
        </div>
    </div>
@endsection
HTML;
    }

    /**
     * 获取blog模块的示例视图内容
     */
    protected function getBlogShowStub()
    {
        return <<<HTML
@extends('theme::layouts.default')

@section('title', '博客文章')

@section('content')
    <div class="card">
        <h2>博客文章</h2>
        <p>这是一个示例页面，您可以根据需要修改它。</p>
    </div>
@endsection
HTML;
    }

    /**
     * 获取示例后台模块视图内容
     */
    protected function getAdminBlogIndexStub()
    {
        return <<<HTML
@extends('public.base')

@section('title', '博客管理')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>博客文章列表</h2>
            <a href="/admin/blog/create" class="btn btn-primary">添加文章</a>
        </div>
        
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>标题</th>
                        <th>作者</th>
                        <th>发布时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>第一篇博客文章</td>
                        <td>Admin</td>
                        <td>2023-05-20</td>
                        <td>
                            <a href="/admin/blog/edit/1" class="btn btn-sm btn-info">编辑</a>
                            <a href="/admin/blog/delete/1" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除吗？')">删除</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>第二篇博客文章</td>
                        <td>Admin</td>
                        <td>2023-05-21</td>
                        <td>
                            <a href="/admin/blog/edit/2" class="btn btn-sm btn-info">编辑</a>
                            <a href="/admin/blog/delete/2" class="btn btn-sm btn-danger" onclick="return confirm('确定要删除吗？')">删除</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
HTML;
    }

    /**
     * 获取示例后台模块视图内容
     */
    protected function getAdminBlogCreateStub()
    {
        return <<<HTML
@extends('public.base')

@section('title', '创建博客文章')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>创建博客文章</h2>
        </div>
        
        <div class="card-body">
            <form action="/admin/blog" method="POST">
                @csrf
                <div class="form-group">
                    <label for="title">标题</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="category">分类</label>
                    <select id="category" name="category_id" class="form-control">
                        <option value="1">技术</option>
                        <option value="2">生活</option>
                        <option value="3">其他</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">内容</label>
                    <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="tags">标签</label>
                    <input type="text" id="tags" name="tags" class="form-control" placeholder="多个标签用逗号分隔">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <a href="/admin/blog" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
HTML;
    }

    /**
     * 获取blog模块的示例视图内容
     */
    protected function getBlogDetailStub()
    {
        return <<<HTML
@extends('blog.public.base')

@section('title', '博客文章详情')

@section('content')
    <div class="card">
        <h2>博客文章标题</h2>
        
        <div class="blog-meta">
            <span>发布时间: 2023-05-20</span>
            <span>作者: Admin</span>
            <span>分类: 技术</span>
        </div>
        
        <div class="blog-content">
            <p>这是博客文章的详细内容...</p>
            <p>可以包含多个段落、图片和其他HTML元素。</p>
        </div>
        
        <div class="blog-tags">
            <span>标签:</span>
            <a href="#">Laravel</a>
            <a href="#">PHP</a>
            <a href="#">Web开发</a>
        </div>
        
        <div class="blog-comments">
            <h3>评论</h3>
            <!-- 评论列表 -->
        </div>
    </div>
@endsection
HTML;
    }
} 