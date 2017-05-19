<?php
/*
  S-Framework 公共函数库
*/

/*C函数，读取用户配置，
   注意：不需要把整个配置文件都读入到一个数组中，会占用内存，在程序中需要用到的地方随时进行读写，所以
   只需要把读过的配置信息存入一个静态数组中即可
 */
/**
 * @param null $config  配置信息键值
 * @param null $value   配置信息值
 */

function C(){
    $conf = \S\Config::getInstance();
//      var_dump($conf->get());
    // var_dump($conf->get());
    $args = func_get_args();
    switch (func_num_args()) {
        case 0: //0个参数，读取全部配置
            return $conf->get();
            break;
        case 1:   //一个参数，则为读取配置信息的值,如果是数组，为动态设置配置信息的值
            if (is_array($args[0])){
                return $conf->setAll($args[0]);
            }
            return $conf->get($args[0]);
            break;
        case 2:   //两个参数，为设置配置信息的值
            //echo "2个参数";
            return $conf->set($args[0],$args[1]);
            break;
        default:
            break;
    }

}

function I($a){
    $b = array_merge($_GET,$_POST);
    return $b[$a];
}
function get($a){
    return $_GET[$a];
}
function post($a){
    return $_POST[$a];
}

function dump($arr){
    if (is_array($arr)){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }else{
        echo $arr;
    }
}

function M($table_name,$dsn = null){
    if (is_null($dsn)){
        $obj = \S\Model::getInstance($table_name);
    }
    return $obj;

}

function import($str){
    $path = C('extend_path') . $str;
    if (file_exists($path)){
        require $path;
        return true;
    }else{
        throw new \S\S_Exception('您要导入的类文件不存在！');
    }
}

function lib($str){
    //这个方法和import类似，用于导入系统内置的类
    $path = S_PATH . 'S/lib/'.$str.'.php';
    if (file_exists($path)){
        require $path;
        return true;
    }else{
        throw new \S\S_Exception("您要导入的内置类不存在！");
    }
}

function error($str="出错了！",$time=5){
    //提示错误信息，跳转时间

}

function session($parm1,$parm2 = null){
    if (is_null($parm2)){
        if (isset($_SESSION[$parm1])){
            return $_SESSION[$parm1];
        }else{
            return false;
        }

    }else{
        $_SESSION[$parm1] = $parm2;
        return true;
    }
}
function redirect($url, $time=0, $msg='') {
    $url = __ROOT__.$url;
    if (empty($msg)){
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    }
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header('refresh:'.$time .';url=' . $url);
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv=\'Refresh\' content=\'".$time .";URL=". $url . "\'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

//smarty中注册的函数，用于在模板中进行跳转
function url($args){
    if (isset($args['k'])&&isset($args['v'])){
        $path =  '/' . $args['k'] . '/'  .$args['v'];
        echo __ROOT__ . 'index.php/' .$args['m'] . '/' . $args['c'] . '/' . $args['a'] . $path;
//
    }else{
        echo __ROOT__. 'index.php/' . $args['m'] . '/' . $args['c'] . '/' . $args['a'] ;
    }
}

function isAjax(){
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
        return true;
    }else{
        return false;
    }
}

function isGet(){
    return $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false;
}

function isPost() {
    return ($_SERVER['REQUEST_METHOD'] == 'POST'  && (empty($_SERVER['HTTP_REFERER']) || preg_replace("~https?:\/\/([^\:\/]+).*~i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("~([^\:]+).*~", "\\1", $_SERVER['HTTP_HOST']))) ? 1 : 0;
}
//得到客户端ip
function getIP()
{
    global $ip;
    if (getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if(getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if(getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
    else $ip = "Unknow";
    return $ip;
}



// 说明：获取完整URL
function getURL()
{
    $pageURL = 'http';

    if (isset($_SERVER["HTTPS"])&&$_SERVER["HTTPS"] == "on")
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80")
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    }
    else
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

