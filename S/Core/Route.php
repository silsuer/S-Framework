<?php
namespace S;


/**
 * Created by PhpStorm.
 * User: silsuer
 * Date: 2017/1/21
 * Time: 14:35
 */
class Route{
    private $module;   //当前模块
    private $controller;  //当前控制器
    private $action;    //当前方法
    public function __construct(){
        $this->parseUrl();//解析路由，获得模块，控制器和操作
        $this->newAction();
    }

    public function parseUrl(){
        switch (C('url_mode')){
            case 1:
                $this->url_mode_1();
                break;
            case 2:
                $this->url_mode_2();
                break;
            default:
                break;
        }
    }

    public function url_mode_1(){
        $this->module = isset($_GET[C('module_name')]) ? $_GET[C('module_name')] : C('default_module');
//          var_dump($this->module);
        $this->controller = isset($_GET[C('controller_name')]) ? $_GET[C('controller_name')] : C('default_controller');
        $this->action = isset($_GET[C('action_name')]) ? $_GET[C('action_name')] : C('default_action');
    }
    public function url_mode_2()
    {
        //获取路径信息（这样的路径有利于搜索引擎收录）
        //  var_dump($_SERVER['PATH_INFO']);
        if (isset($_SERVER['PATH_INFO'])) {
            $paths = explode(C('path_separator'), trim($_SERVER['PATH_INFO'], '/'));
            //var_dump($paths);
            //删除数组中第一个元素，并返回这个元素的值，所以这样就陆续获取到了模块、控制器、操作
            $url_module = array_shift($paths);
            $url_controller = array_shift($paths);
            $url_action = array_shift($paths);
            //传值操作（获取get参数）
            //var_dump($paths);
            for ($i = 0; $i < count($paths); $i += 2) {
                if (isset($paths[$i + 1])) {
                    $_GET[$paths[$i]] = $paths[$i + 1];
//                    var_dump($_GET);
                }else {
                    throw new S_Exception($paths[$i] . '未设置一个参数值');
                }
            }
//        }
            $this->module = !empty($url_module) ? $url_module : C('default_module');
            //通过判断url里面是否存在c参数，如果没有则设为默认控制器
            $this->controller = !empty($url_controller) ? $url_controller : C('default_controller');
            $this->action = !empty($url_action) ? $url_action : C('default_action');
//            echo $this->module."<br>";
//            echo $this->controller."<br>";
//            echo $this->action."<br>";
        }else{
            $this->module = C('default_module');
            $this->controller = C('default_controller');
            $this->action = C('default_action');
        }
//        echo $this->module."<br>";
//        echo $this->controller."<br>";
//        echo $this->action."<br>";
    }

    public function newAction(){
        $path =  APP_PATH . $this->module . '/Controller/' .$this->controller . 'Controller.php';

        if (file_exists($path)){
            $controllerName = '\\' . $this->module . '\\' . $this->controller . 'Controller';
        }else{
            throw new S_Exception($controllerName . '控制器类文件不存在');
        }
        if (class_exists($controllerName)){
            $controllerObj = new $controllerName;
        }else{
            throw new S_Exception($controllerName . '控制器类不存在，请检查类名或命名空间');
        }
        if (method_exists($controllerName,$this->action)){
            $controllerObj->{$this->action}();
        }else{
            throw new S_Exception($this->action . '方法不存在');
        }
    }
}