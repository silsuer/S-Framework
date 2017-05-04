<?php
/**
 * Created by PhpStorm.
 * User: silsuer
 * Date: 2017/1/20
 * Time: 19:12
 */
namespace S;


class S{
    private static $prefixes = [];
    public  static function run(){
        //应该做的是：设置字符集，系统类映射，自动加载注册方法，实例化路由
        self::setHeader();
        self::getMapList();
        spl_autoload_register('self::s_autoload');
//        Route::start();
        try{
            new Route();
        }catch (S_Exception $e){
            $e->getDetail();
        }
    }

//    private static function isSsl()
//    {
//        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
//            return true;
//        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
//            return true;
//        }
//        return false;
//    }

    private static function setHeader(){
        header("Content-type:text/html;Charset=".C('default_charset'));
        date_default_timezone_set(C('default_timezone'));
    }

    public static function addNamespace($prefix, $base_dir, $prepend = false)
    {
        //格式化命名空间前缀，以反斜杠结束（去除两侧的反斜杠，只在最后加一个反斜杠）
        $prefix = trim($prefix, '\\') . '\\';
        //格式化基目录以正斜杠结尾，DIRECTORY_SEPARATOR是系统常量，目录分隔符，把基目录右侧斜杠去掉，换成系统支持的斜杠，然后最后统一为正斜杠
        $base_dir = rtrim($base_dir, '/') . DIRECTORY_SEPARATOR;
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';
        //初始化命名空间前缀数组
        //如果前缀已存在数组中则跳过，否则存入数组
        if (isset(self::$prefixes[$prefix]) === false) {
            self::$prefixes[$prefix] = [];
        }

        if ($prepend) {
            //命名空间前缀相同时，后增基目录（array_unshift() 函数用于向数组插入新元素。新数组的值将被插入到数组的开头。）
            array_unshift(self::$prefixes[$prefix], $base_dir);
        } else {
            //前增，向数组尾部增加值
            array_push(self::$prefixes[$prefix], $base_dir);
        }
    }


    private static function getMapList()
    {
        //实例化Config类，执行get函数，获取到namespace_map_list的值，循环更改$prefixes的值
        foreach (Config::getInstance()->get('namespace_map_list') as $key => $value) {
            self::addNamespace($key, $value);
        }
//        $vaolu =
    }

    private static function s_autoload($className){
        // 当前命名空间前缀
        $prefix = $className;

        //从后面开始遍历完全合格类名中的命名空间名称，来查找映射的文件名
        //strpos获取参数2在参数1中最后出现的位置，substr截取字符串
        while (false !== $pos = strrpos($prefix, '\\')) {
            // 命名空间前缀
            $prefix = substr($className, 0, $pos + 1);
            // 相对的类名
            $relative_class = substr($className, $pos + 1);
            //尝试加载与映射文件相对的类
            $mapped_file = self::loadMappedFile($prefix, $relative_class);
            //  var_dump($mapped_file);
            if ($mapped_file) {
                return $mapped_file;
            }
            //去除前缀的反斜杠
            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

    private static function loadMappedFile($prefix, $relative_class)
    {
        //这个命名空间前缀是否存在基本的目录？

        if (isset(self::$prefixes[$prefix]) === false) {
            return false;
        }

        $relative_class = str_replace('\\', '/', $relative_class);

        foreach (self::$prefixes[$prefix] as $base_dir) {
            $file = $base_dir . $relative_class . '.php';
            // 如果映射文件存在就加载它
            if (self::requireFile($file)) {
                return true;
            }
        }
        return false;
    }

    private static function requireFile($file)
    {
        if (file_exists($file)) {
            include $file;
            return true;
        }
        return false;
    }

}
