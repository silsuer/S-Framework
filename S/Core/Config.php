<?php
namespace S;
/**
 * Created by PhpStorm.
 * User: silsuer
 * Date: 2017/1/20
 * Time: 18:23
 * Class Config:  配置文件操作类
 */
class Config{
    //这个数组是用来存放配置值的
    private $config=[];
    //这个变量用来存放单例的
    private static $instance;
//    public $value;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Config constructor. 构造函数 创建实例时就引入配置文件，并合并，给$config赋值
     */
    private function __construct(){
        $sys_conf = [];
        $user_conf = [];
        //系统配置文件
        if (file_exists(SYS_CONFIG)){
            $sys_conf = include(SYS_CONFIG);
        }
        //用户配置文件
        if (file_exists(USER_CONFIG)){
            $user_conf = include(USER_CONFIG);
        }
//        var_dump(array_merge($sys_conf,$user_conf));
        return  $this->config = array_merge($sys_conf,$user_conf);
    }

    /**
     * @return array  获取config文件中的数据
     */
    public function get($parm = null){
        $value = [];
        if (isset($this->config) && empty($parm)){
            return $this->config;
        }

//        if (is_array($this->config[$parm])){
//            return $this->config[$parm]
//        }
        if (isset($this->config[$parm])){
            return $this->config[$parm];
        }else{
            echo 'config参数错误';
        }


    }

    public function  setAll($arr){

        if (is_array($arr)){
            foreach ($arr as $key => $value) {
                $this->set($key,$value);
            }
            return true;
        }else{
            return false;
        }
    }

    public function  set($keys,$values){
        $this->config[$keys] = $values;
        return true;
    }

}