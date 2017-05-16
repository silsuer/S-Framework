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

  /*模版路径配置*/
  'template'=>[
       'default'=>['Application/','/View/','/'],//以数组形式，每个值中间用模块名，控制器类名填充
  ],
  'cache'=>[  //缓存配置
      'path'=>  'Cache/',   //缓存目录
      'encryption'=>'md5',   //缓存文件默认命名方式
      'time'=>3          //缓存时间,默认是30s
  ],
  'session' => true,
);

