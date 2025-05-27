<?php

namespace Fastcmf\Modules\Http\Controllers;

use Illuminate\Routing\Controller;
use Fastcmf\Modules\Facades\Theme;

class HomeBaseController extends Controller
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
     * 成功响应
     */
    protected function success($message = '', $data = [], $code = 200)
    {
        return response()->json([
            'code' => $code,
            'msg' => $message,
            'data' => $data,
        ]);
    }

    /**
     * 错误响应
     */
    protected function error($message = '', $code = 400, $data = [])
    {
        return response()->json([
            'code' => $code,
            'msg' => $message,
            'data' => $data,
        ]);
    }

    /**
     * 获取当前主题
     */
    protected function getTheme()
    {
        return Theme::current();
    }
} 