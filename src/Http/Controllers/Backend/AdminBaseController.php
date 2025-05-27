<?php

namespace Fastcmf\Modules\Http\Controllers\Backend;

use Fastcmf\Modules\Http\Controllers\BaseController;

class AdminBaseController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 后台控制器初始化逻辑
        $this->middleware(['web', 'auth']);
        $this->middleware('permission:access-admin')->except('logout');
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
        // 添加后台全局变量
        $data['admin_name'] = config('admin.name', 'FastCMF管理系统');
        $data['admin_version'] = config('admin.version', '1.0.0');
        $data['admin_user'] = auth()->user();
        
        return view($view, $data, $mergeData);
    }
    
    /**
     * 后台成功消息
     *
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function adminSuccess($message = '操作成功')
    {
        return back()->with('success', $message);
    }
    
    /**
     * 后台错误消息
     *
     * @param string $message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function adminError($message = '操作失败')
    {
        return back()->with('error', $message)->withInput();
    }
} 