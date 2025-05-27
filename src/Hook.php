<?php
// +----------------------------------------------------------------------
// | Hook.php模块业务逻辑
// +----------------------------------------------------------------------
// | Author: LuYuan 758899293@qq.com
// +----------------------------------------------------------------------
namespace Fastcmf\Modules;

class Hook
{
    protected $listeners = [];

    /**
     * 添加钩子监听器
     */
    public function listen($hook, $callback)
    {
        if (!isset($this->listeners[$hook])) {
            $this->listeners[$hook] = [];
        }

        $this->listeners[$hook][] = $callback;
    }

    /**
     * 触发钩子
     */
    public function fire($hook, $params = [])
    {
        $results = [];

        if (isset($this->listeners[$hook])) {
            foreach ($this->listeners[$hook] as $callback) {
                $results[] = call_user_func_array($callback, [$params]);
            }
        }

        // 同时触发Laravel事件
        event("module.hook.{$hook}", $params);

        return $results;
    }
}