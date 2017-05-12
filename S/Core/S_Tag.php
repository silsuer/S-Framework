<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 22:17
 * 标签类，用于自定义属于自己的标签解析类
 * 标签类的原理：在Controller中的display方法是用来渲染模版的，
 * display方法会require对应的模版文件，然后new一个标签类tag对象
 * 调用tag类对象的解析函数analyze，这个函数是用正则来分析出所有的匹配{S:xxx xxx xxx}的数据
 * 然后获取中间x的部分，把“：”到第一个空格的部分分析出来，看是否在标签列表中存在，如果存在，证明可以解析
 * 如果不存在，就跳出；不解析这个数据；然后根据标签名，调用对应的具体标签函数(这些函数会返回一个字符串）
 * 用这个字符串去替换正则匹配出来的数据
 */

namespace S;


class S_Tag
{
   //标签列表
    private $tagList = array(
        "php" => "phpStart",
        "PHP" => "phpStart",
        "/php"=>"phpEnd",
        "/PHP" => "phpEnd",
        "foreach" =>"foreachStart",
        "/foreach" =>"foreachEnd",
        "if"=>"ifStart",
        "/if"=>"ifEnd"
    );
    //检测标签是否存在
    //标签解析
    public function parse($mattches){  //标签解析函数，传入一个数组，遍历数组分别解析

        $parseResult = [];  //结果数组
        foreach ($mattches as $k => $v){
            $parseResult[$v] = $this->parseTag($v);
        }
        return $parseResult;
    }

    public function parseTag($str){

        $s = ""; //要返回的字符串，默认是空
        $s = substr($str,0,strlen($str)-1);
        $s = substr($s,3);
        $a = explode(' ',$s);
        $t = "";
        if(is_array($a)){
            $t = $a[0];
        }else{
            $t = $a;
        }
        //此时$t可能是标签名，或者是闭合标签
        if(isset($this->tagList[$t])){  //如果在标签列表中存在这个标签，就调用，否则就抛出提示信息
           echo $this->tagList[$t];
        }else{
            throw new S_Exception("使用了错误的标签 ".$t.",该标签可能未注册！");
        }
        //echo $s;
       // dump($a);
    }
}