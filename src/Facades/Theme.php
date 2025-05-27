<?php

namespace Fastcmf\Modules\Facades;

use Illuminate\Support\Facades\Facade;

class Theme extends Facade
{
    /**
     * 获取组件的注册名称
     */
    protected static function getFacadeAccessor()
    {
        return 'theme';
    }
} 