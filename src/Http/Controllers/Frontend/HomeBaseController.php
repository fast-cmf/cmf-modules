<?php

namespace Fastcmf\Modules\Http\Controllers\Frontend;

use Fastcmf\Modules\Http\Controllers\BaseController;

class HomeBaseController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 前台控制器初始化逻辑
        $this->middleware('web');
    }
    
    /**
     * 渲染视图
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Illuminate\View\View
     */
    protected function view($view, $data = [], $mergeData = [])
    {
        // 添加全局变量
        $data['site_name'] = config('app.name');
        $data['site_url'] = url('/');
        
        return view($view, $data, $mergeData);
    }
} 