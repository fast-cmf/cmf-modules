<?php

namespace Fastcmf\Modules\Http\Controllers;

use Illuminate\Routing\Controller;
use Fastcmf\Modules\Facades\Theme;
use Illuminate\Support\Str;

class AdminBaseController extends Controller
{
    /**
     * 视图数据
     */
    protected $viewData = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 初始化操作
        $this->middleware('auth');
    }

    /**
     * 设置视图数据
     */
    protected function assign($key, $value = null)
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }

    /**
     * 渲染视图
     */
    protected function view($template = '', $data = [])
    {
        $data = array_merge($this->viewData, $data);
        
        // 如果模板为空，自动获取当前控制器和方法对应的视图
        if (empty($template)) {
            $template = $this->getAutoTemplate();
        }
        // 如果模板以/开头，表示从主题根目录加载
        elseif (strpos($template, '/') === 0) {
            $template = 'admin::' . substr($template, 1);
        }
        
        return view($template, $data);
    }
    
    /**
     * 自动获取视图模板
     */
    protected function getAutoTemplate()
    {
        // 获取当前请求的控制器和方法
        $action = app('request')->route()->getActionName();
        
        // 解析控制器类名和方法名
        list($controller, $method) = explode('@', $action);
        
        // 获取控制器短名称（不含命名空间）
        $controller = class_basename($controller);
        
        // 移除Controller后缀
        $controller = str_replace('Controller', '', $controller);
        
        // 转换为蛇形命名
        $controller = Str::snake($controller);
        
        // 获取当前模块名
        $moduleName = $this->getModuleName();
        
        // 构建视图路径
        return "admin::{$moduleName}/{$controller}/{$method}";
    }
    
    /**
     * 获取当前模块名
     */
    protected function getModuleName()
    {
        // 获取当前控制器的类名
        $class = get_class($this);
        
        // 解析命名空间
        $parts = explode('\\', $class);
        
        // 模块名应该是命名空间的第二部分
        if (count($parts) >= 3 && $parts[0] === 'App') {
            return Str::snake($parts[1]);
        }
        
        // 如果无法确定模块名，返回默认值
        return 'common';
    }

    /**
     * 后台成功响应
     */
    protected function adminSuccess($message = '', $data = [], $url = '', $wait = 3)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'code' => 1,
                'msg' => $message,
                'data' => $data,
                'url' => $url,
                'wait' => $wait,
            ]);
        }

        return redirect($url ?: url()->previous())->with('success', $message);
    }

    /**
     * 后台错误响应
     */
    protected function adminError($message = '', $data = [], $url = '', $wait = 3)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'code' => 0,
                'msg' => $message,
                'data' => $data,
                'url' => $url,
                'wait' => $wait,
            ]);
        }

        return redirect($url ?: url()->previous())->with('error', $message);
    }

    /**
     * 获取当前主题
     */
    protected function getTheme()
    {
        return Theme::adminCurrent();
    }
} 