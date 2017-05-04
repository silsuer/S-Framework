<?php
namespace S;
/*公共入口文件*/
//定义框架版本
define('S_VERSION', 'V1.0');

//定义当前脚本执行的绝对路径
defined('S_PATH') or define('S_PATH',dirname($_SERVER['SCRIPT_FILENAME']).'/');

//定义常量，控制框架是否开启调试模式,默认是false
defined('APP_DEBUG') or define('APP_DEBUG', false);
if (APP_DEBUG==true){
    error_reporting(E_ALL);
}else{
    error_reporting(0);
}

//定义控制器类文件后缀
defined('CONTROLLER_EXT') or define('APP_EXT', 'Controller.class.php');


//定义是否是CGI模式
define('IS_CGI',(0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );

//定义是否是windows系统
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );

//定义是否是CLI模式
define('IS_CLI',PHP_SAPI=='cli'? 1 : 0);

//根据CLI模式定义  __ROOT__
if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('__ROOT__',  (($_root=='/' || $_root=='\\')?'':$_root.'/'));
    }
}
/**
 * 这里有一个win和linux的资源定界符不一样的问题，win是\  linux是/ 所以需要根据IS_WIN,替换里边的定界符（暂时保留问题 先不解决）
 */
//定义系统应用所在路径
defined('APP_PATH') or define('APP_PATH', S_PATH . 'Application/');
//defined('APP_PATH') or define('APP_PATH', __ROOT__ . 'Application/');
//echo APP_PATH;
//echo S_PATH.__ROOT__;

//定义运行时的核心目录
defined('CORE_PATH') or define('CORE_PATH',S_PATH . 'S/Core/');


//defined('EXTEND_PATH') or define('__EXTEND__' . S_PATH . 'S/Extend/');

defined('SYS_CONFIG') or define('SYS_CONFIG',CORE_PATH . 'Common/config.php');

//用户自定义配置目录
defined('USER_COMMON') or define('USER_COMMON',APP_PATH . 'Common');

defined('USER_CONFIG') or define('USER_CONFIG','./Application/Common/config.php');



//引入系统配置类
include(CORE_PATH . 'Config.php');

//引入系统函数库
include(CORE_PATH . 'Common/functions.php');

if (C('session')==true){
    session_start();
}

//引入系统加载函数
include(CORE_PATH . 'S.php');
S::run();
//$s = M('user')->select('id');
//$s2 = M('user')->select(array('id','user_name'));
//dump($s);
//dump($s2);


