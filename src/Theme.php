<?php
// +----------------------------------------------------------------------
// | Theme.php模块业务逻辑
// +----------------------------------------------------------------------
// | Author: LuYuan 758899293@qq.com
// +----------------------------------------------------------------------
namespace Fastcmf\Modules;

use Illuminate\Support\Facades\File;

class Theme
{
    protected $name;
    protected $path;

    // ... 主题相关方法 ...

    /**
     * 获取当前主题
     */
    public static function current()
    {
        return config('themes.current', 'default');
    }
}