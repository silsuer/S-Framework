<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 22:05
 * 控制器基类，用于解析标签等操作
 */

namespace S;


class Controller
{

     private $arr = []; //要渲染的数据
    /*文件渲染方法
     *传入路径，默认是空
     */
     protected function display($path = null){
         if (is_null($path)){
           //如果是空，直接抛出异常
             throw new S_Exception("display()方法的参数不能为空！");
         }
         //如果不是空，先判断有没有"/"符号，如果有，证明是完整路径，直接传参，如果没有，调用默认配置
         if (strstr($path,"/")){
             $this->requiredTpl($path);
         }else{
             $class = $this->getCallInfo();
             $tempCfg = C('template');
             if(isset($tempCfg[$class[0]])){
                 $p = $tempCfg[$class[0]][0].$class[0].$tempCfg[$class[0]][1].$class[1].$tempCfg[$class[0]][2].$path;
             }else{
                 $p = $tempCfg['default'][0].$class[0].$tempCfg['default'][1].$class[1].$tempCfg['default'][2].$path; //根据路径配置拼接出的路径，形如
             }
             $this->requiredTpl($p);
         }
   }

    private function requiredTpl($path = null){
         if(is_null($path)){
             //如果引入路径是空，就抛出异常
             throw new S_Exception("引入路径为空");
         }
        //先载入缓存配置，然后判断是否是调试模式，如果不是的话，就按照缓存配置路径写入缓存文件，然后按照
        $cacheCfg = C('cache');
        $p = $path;
        $class = $this->getCallInfo();  //获取模块名和控制器名
        $encry_p = $cacheCfg['encryption']($p).'.html';  //获取加密后的缓存文件,默认是md5加密
        $requirePath =$cacheCfg['path'].$class[0].'/'.$class[1] . '/' . $encry_p;//拼接出缓存文件地址

        /*判断是否是调试模式*/
        if(APP_DEBUG==true){
            //是调试模式，就不缓存（不判断缓存创建时间，直接覆盖原来的缓存文件）
            $content = file_get_contents(S_PATH . $path); //获取所有模版文件中所有内容
            //这里进行正则解析
            $contents = $this->parseMattches($content);
            try{
                $createFileName=$cacheCfg['path'].$class[0].'/'.$class[1];  //创建文件
                if (!file_exists($createFileName)){
                    $this->mkdirs($createFileName);
                }
                $createFileName .= '/'.$encry_p;
                $fp = fopen($createFileName,'w');
                fwrite($fp,$contents);
                fclose($fp);
            }catch (S_Exception $e){
                throw new S_Exception('写入缓存时失败！');
            }
            $this->includeFile($requirePath);
        }else{
            //不是调试模式，进行缓存
            //判断这个缓存文件是否存在&&是否没有超过缓存时间
            if(file_exists(S_PATH . $requirePath)&&((time()-filemtime( S_PATH .$requirePath))<$cacheCfg['time'])){
                //这里直接把对应的缓存文件require进来
                $this->includeFile($requirePath);
            }else{
                //这里重新创建缓存文件
                $content = file_get_contents(S_PATH . $path); //获取所有模版文件中所有内容
                //这里把内容正则解析完成，准备写入缓存文件
                $contents = $this->parseMattches($content);
                try{
                    $createFileName=$cacheCfg['path'].$class[0].'/'.$class[1];  //创建文件
                    if (!file_exists($createFileName)){
                        $this->mkdirs($createFileName);
                    }
                    $createFileName .= '/'.$encry_p;
                    $fp = fopen($createFileName,'w');
                    fwrite($fp,$contents);
                    fclose($fp);
                }catch (S_Exception $e){
                    throw new S_Exception('写入缓存时失败！');
                }

                //引入缓存文件
                $this->includeFile($requirePath);
            }
        }
    }


    private function getCallInfo(){
        $class = explode("\\",get_class($this)); //获得当前类名；形如Home\IndexController
        $class[1] = substr($class[1],0,-10);  //此时模块名和控制器名都获取到了
        return $class;
    }

    private function includeFile($p){  //使用两种方式来进行include所需的文件
        try{
            include(S_PATH . $p);
        }catch (S_Exception $e){
            try{
                include(__ROOT__.$path);
            }catch (S_Exception $e){
                throw new S_Exception("模版不存在！路径：".__ROOT__.$path);
            }
        }
    }

    private function mkdirs($dir){
        if(!is_dir($dir)){
            if(!$this->mkdirs(dirname($dir))){
                exit("不能创建目录");
            }
            if(!mkdir($dir,0777)){
                exit("不能创建目录2");
            }
        }
        return true;
    }

     function __set($k, $v)
    {
        // TODO: Implement __set() method.
        $this->arr[$k] = $v;
    }

     function __get($name)
    {
        // TODO: Implement __get() method.
    }

    private  function  parseMattches($content){ //正则解析content中的字符串，并把解析后的字符串返回
        $mattches = [];  //搜索结果
        $parseTag = [];  //经过标签解析函数解析过的数据数组
        $con = serialize($this->arr);
        $c = "<?php $"."arr = unserialize('".$con ."'); foreach(\$arr as \$k => \$v){\$"."\$k=\$v;"." } ?>";

        $patter = '/(\{S\:).*\}/';  //正则匹配字符串，匹配形如{S:foreach name='data' item='v'}的字符
        preg_match_all($patter,$content,$mattches);
        //dump($mattches[0]); //对结果排序使 $matches[0] 为全部模式匹配的数组，$matches[1] 为第一个括号中的子模式所匹配的字符串组成的数组，以此类推。
        //str_replace(array,new_array,subject) 可以进行批量替换
        $tag = new S_Tag();
        $parseTag = $tag->parse($mattches[0]);//对解析出来的正则进行再次解析，解析成php代码
        $content = str_replace($mattches[0],$parseTag,$content);
        $content =$c . $content ;
        return $content;
    }
}