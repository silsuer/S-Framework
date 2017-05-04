<?php
return array(
    'name' => 'silsuer',
    'password' =>'asdasd',
    'default_charset'=>'UTF-8',
    'default_timezone'=>'PRC',
    'namespace_map_list' => [
        'S' => S_PATH . 'S/core',
        'Home'    => S_PATH . 'Application/Home/Controller',
        //'test' => ROOT_PATH.'test',
    ],

    'extend_path' => S_PATH . 'S/Extend/',

    'url_mode'=>2,   //URL模式

    'module_name' =>'m', //默认模块参数名  index.php?m=Home&c=Index&a=index
    'default_module' =>'Home',
    'controller_name' =>'c',
    'default_controller'=>'Index',
    'action_name'=>'a',
    'default_action'=>'index',

    'path_separator' =>'/',

    'database'=>[
        'db_host' => 'localhost',
        'db_name' => 'ceshi',
        'db_user' =>'root',
        'db_password' => '',
        'db_prefix' =>'ceshi_',
        'db_charset' => 'utf8'
    ],

  'session' => true,
);

