# Laravel 模块化扩展包

这个扩展包为Laravel 12提供模块化功能，允许您将应用拆分为多个模块，每个模块都有自己的控制器、视图、路由等。同时还提供了主题系统和钩子系统。

## 安装

通过Composer安装:

```bash
composer require fastcmf/laravel-modules
```

安装后，发布配置文件:

```bash
php artisan vendor:publish --provider="Fastcmf\Modules\Providers\ModulesServiceProvider" --tag="config"
```

## 模块功能

### 创建新模块

```bash
php artisan make:module Blog
```

这将创建一个名为Blog的新模块，包含以下目录结构:

```
app/Modules/Blog/
├── Controllers/
│   └── BlogController.php
├── Models/
├── Providers/
│   └── BlogServiceProvider.php
├── Routes/
│   ├── api.php
│   └── web.php
├── Views/
└── module.json
```

### 访问模块

默认情况下，模块路由可通过`/blog`访问。

### 在模块中开发

1. 创建控制器:

```php
namespace App\Modules\Blog\Controllers;

use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function index()
    {
        return view('blog::posts.index');
    }
}
```

2. 创建视图:

在`app/Modules/Blog/Views`目录中创建视图文件。

3. 定义路由:

编辑`app/Modules/Blog/Routes/web.php`添加更多路由。

### 启用/禁用模块

```php
// 启用模块
\Module::enable('Blog');

// 禁用模块
\Module::disable('Blog');
```

### 模块依赖

在`module.json`文件中定义依赖:

```json
{
    "name": "Blog",
    "dependencies": ["User", "Comment"]
}
```

## 主题系统

### 创建新主题

```bash
php artisan make:theme mytheme
```

这将创建一个名为mytheme的新主题，包含以下目录结构:

```
resources/themes/mytheme/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   └── images/
├── views/
│   ├── layouts/
│   │   └── default.blade.php
│   └── index.blade.php
├── lang/
├── config/
└── theme.json
```

### 使用主题

在控制器中:

```php
public function index()
{
    // 切换到指定主题
    \Theme::set('mytheme');
    
    // 使用主题视图
    return view('theme::index');
}
```

或者使用中间件:

```php
// 在路由中应用主题
Route::get('/', 'HomeController@index')->middleware('theme:mytheme');
```

### 获取主题资源

在Blade模板中:

```php
<link rel="stylesheet" href="{{ theme()->asset('css/style.css') }}">
<script src="{{ theme()->asset('js/app.js') }}"></script>
<img src="{{ theme()->asset('images/logo.png') }}">
```

## 钩子系统

### 注册钩子

在模块的`hooks.php`文件中:

```php
<?php

use Fastcmf\Modules\Facades\Hook;

// 添加钩子监听器
Hook::listen('user.registered', function($user) {
    // 处理用户注册事件
    \Log::info('新用户注册: ' . $user->email);
    return $user;
}, 10);

// 添加过滤器
Hook::listen('post.content', function($content) {
    // 过滤帖子内容
    return str_replace('坏词', '***', $content);
});
```

### 触发钩子

在应用代码中:

```php
// 触发钩子并传递参数
$user = Hook::trigger('user.registered', $user);

// 过滤内容
$content = Hook::trigger('post.content', $content);

// 只获取第一个返回值
$result = Hook::one('some.hook', $data);
```

### 内置钩子

扩展包提供了以下内置钩子:

- `modules.init` - 模块初始化时
- `modules.boot` - 所有模块启动完成后
- `modules.discover.before` - 模块发现前
- `modules.discover.after` - 模块发现后
- `module.discovered` - 单个模块被发现时
- `module.load.before` - 模块加载前
- `module.load.after` - 模块加载后
- `module.boot.before` - 模块启动前
- `module.boot.after` - 模块启动后
- `module.boot.failed` - 模块启动失败
- `module.hooks.registered` - 模块钩子注册后
- `module.enabled` - 模块启用时
- `module.disabled` - 模块禁用时
- `module.install.before` - 模块安装前
- `module.install.after` - 模块安装后
- `module.uninstall.before` - 模块卸载前
- `module.uninstall.after` - 模块卸载后 