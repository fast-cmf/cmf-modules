<?php
// +----------------------------------------------------------------------
// | Module.php模块业务逻辑
// +----------------------------------------------------------------------
// | Author: LuYuan 758899293@qq.com
// +----------------------------------------------------------------------
namespace Fastcmf\Modules\Facades;

use Illuminate\Support\Facades\Facade;

class Module extends Facade
{
    /**
     * 获取组件的注册名称
     */
    protected static function getFacadeAccessor()
    {
        return 'modules';
    }


}