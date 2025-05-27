<?php

namespace Fastcmf\Modules;

use Illuminate\Support\Facades\Event;

class Hook
{
    /**
     * 已注册的监听器
     */
    protected $listeners = [];

    /**
     * 单例实例
     */
    protected static $instance;

    /**
     * 获取单例实例
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 添加钩子监听器
     *
     * @param string $hook 钩子名称
     * @param callable $callback 回调函数
     * @param int $priority 优先级，数字越小优先级越高
     * @return void
     */
    public function addListener($hook, $callback, $priority = 10)
    {
        if (!isset($this->listeners[$hook])) {
            $this->listeners[$hook] = [];
        }

        if (!isset($this->listeners[$hook][$priority])) {
            $this->listeners[$hook][$priority] = [];
        }

        $this->listeners[$hook][$priority][] = $callback;
    }

    /**
     * 移除钩子监听器
     *
     * @param string $hook 钩子名称
     * @param callable|null $callback 回调函数，如果为null则移除所有监听器
     * @return void
     */
    public function removeListener($hook, $callback = null)
    {
        if (!isset($this->listeners[$hook])) {
            return;
        }

        if ($callback === null) {
            unset($this->listeners[$hook]);
            return;
        }

        foreach ($this->listeners[$hook] as $priority => $listeners) {
            foreach ($listeners as $key => $listener) {
                if ($listener === $callback) {
                    unset($this->listeners[$hook][$priority][$key]);
                }
            }

            if (empty($this->listeners[$hook][$priority])) {
                unset($this->listeners[$hook][$priority]);
            }
        }

        if (empty($this->listeners[$hook])) {
            unset($this->listeners[$hook]);
        }
    }

    /**
     * 触发钩子
     *
     * @param string $hook 钩子名称
     * @param mixed $args 参数
     * @return mixed
     */
    public function fire($hook, $args = null)
    {
        $result = $args;

        // 同时触发Laravel事件
        Event::dispatch("modules.hook.{$hook}", [$args]);

        if (!isset($this->listeners[$hook])) {
            return $result;
        }

        // 按优先级排序
        ksort($this->listeners[$hook]);

        foreach ($this->listeners[$hook] as $listeners) {
            foreach ($listeners as $listener) {
                $value = call_user_func($listener, $result);
                
                if (!is_null($value)) {
                    $result = $value;
                }
            }
        }

        return $result;
    }

    /**
     * 检查钩子是否存在
     *
     * @param string $hook 钩子名称
     * @return bool
     */
    public function hasListeners($hook)
    {
        return isset($this->listeners[$hook]);
    }

    /**
     * 获取所有监听器
     *
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * 添加钩子监听器（静态方法）
     */
    public static function listen($hook, $callback, $priority = 10)
    {
        return static::getInstance()->addListener($hook, $callback, $priority);
    }

    /**
     * 触发钩子（静态方法）
     */
    public static function trigger($hook, $args = null)
    {
        return static::getInstance()->fire($hook, $args);
    }

    /**
     * 获取第一个钩子返回值（静态方法）
     */
    public static function one($hook, $args = null)
    {
        $instance = static::getInstance();
        
        if (!$instance->hasListeners($hook)) {
            return $args;
        }
        
        // 按优先级排序
        ksort($instance->listeners[$hook]);
        
        foreach ($instance->listeners[$hook] as $listeners) {
            foreach ($listeners as $listener) {
                $value = call_user_func($listener, $args);
                
                if (!is_null($value)) {
                    return $value;
                }
            }
        }
        
        return $args;
    }
} 