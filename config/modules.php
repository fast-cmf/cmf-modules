<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 模块路径
    |--------------------------------------------------------------------------
    |
    | 指定模块存储位置
    |
    */
    'path' => app_path(),

    /*
    |--------------------------------------------------------------------------
    | 模块命名空间
    |--------------------------------------------------------------------------
    |
    | 模块的命名空间前缀
    |
    */
    'namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | 模块结构
    |--------------------------------------------------------------------------
    |
    | 定义模块结构目录
    |
    */
    'structure' => [
        'controllers' => 'Http/Controllers',
        'providers' => 'Providers',
        'routes' => 'Routes',
        'models' => 'Models',
        'migrations' => 'Database/Migrations',
        'seeders' => 'Database/Seeders',
    ],

    /*
    |--------------------------------------------------------------------------
    | 自动发现模块
    |--------------------------------------------------------------------------
    |
    | 是否自动发现模块
    |
    */
    'auto_discover' => true,
]; 