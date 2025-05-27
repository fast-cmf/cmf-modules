<?php

namespace Fastcmf\Modules\Http\Controllers;

use Illuminate\Routing\Controller;
use Fastcmf\Modules\Facades\Theme;

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
    protected function view($template, $data = [])
    {
        $data = array_merge($this->viewData, $data);
        return view($template, $data);
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
        return Theme::current();
    }
} 