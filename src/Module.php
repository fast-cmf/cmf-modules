<?php
// +----------------------------------------------------------------------
// | Module.php模块业务逻辑
// +----------------------------------------------------------------------
// | Author: LuYuan 758899293@qq.com
// +----------------------------------------------------------------------

class Module
{
    /**
     * 模块名称
     */
    protected $name;

    /**
     * 模块路径
     */
    protected $path;

    /**
     * 构造函数
     */
    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * 获取模块名称
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取模块路径
     */
    public function getPath()
    {
        return $this->path;
    }
}