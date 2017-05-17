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
        "/php"=>"CommonEnd",
        "foreach" =>"foreachStart",
        "/foreach" =>"CommonEnd",
        "if"=>"ifStart",
        "/if"=>"ifEnd",
        "elseif"=>"elseifStart",
        "else"=>"elseStart"
    );
    //检测标签是否存在
    //标签解析
    public function parse($mattches){  //标签解析函数，传入一个数组，遍历数组分别解析

        $parseResult = [];  //结果数组
        foreach ($mattches as $k => $v){
            $parseResult[$k] = $this->parseTag($v);
        }
        return $parseResult;
    }

    public function parseTag($str){
        $code = "";//要返回的字符串，默认是空
        $s = "";
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
           $code = $this->{$this->tagList[$t]}($str);
        }else if(substr($t,0,1)=="$"){
            $t = trim($t,"$");//去掉开始的$符号
            //代表是变量$v.id
            if(strpos($t,'.')){ //如果包含小数点，证明这是二维数组，否则是一位数组，直接打印就可以

                $tarr = explode('.',$t);
                $code =  "<?php echo $".$tarr[0]."[" . $tarr[1] . "] ?>";
            }else{
                $code = "<?php echo $".$t." ?>";
            }

        }else{
            throw new S_Exception("使用了错误的标签 ".$t.",该标签可能未注册！");
        }
        return $code;
    }

    private function foreachStart($str){
        //echo $str;
        $s = substr($str,0,strlen($str)-1);
        $s = substr($s,3+strlen("foreach"));
        $arr = explode(' ',trim($s)); //以空格分割数组
        $parms = [];
        //遍历数组，以等号分割参数
        foreach ($arr as $v){

            $parm = explode('=',$v);
            $parms[$parm[0]] = $parm[1];
        }
        $code = "";
        $code .="<?php ";
        $code .="foreach($".trim($parms['name'],'\'') . " as ";
        $code .= isset($parms['key']) ? "$".trim($parms['key'],'\'')."=>" : "";
        $code .= "$".trim($parms['item'],'\'') . " ){";
        $code .="?>";
        //echo $code;die();
        return $code;
    }

    private function CommonEnd($str){
        $code = "<?php } ?>";
        return $code;
    }



    private function ifStart($str){
     /*
      *  {S:if ($d > 2)}大于2
         {S:elseif ($d == 2)}等于2
         {S:else}小于2
         {S:/if}
      * */
       /*
        * if($a>2){
        * echo "大于2";
        * }else if($a==2){
        * echo "等于2";
        * }else{
        * echo "小于2";
        * }*/

        $s = substr($str,0,strlen($str)-1);
        $s = substr($s,3+strlen("if"));  //去掉了前后标签
        //判断是否是被（）包裹起来的，如果是的话，就把整体放入if的空格里
        $code = "";
        if((substr(trim($s),0,1)=="(")&&(substr(trim($s),-1)==")") ){
            $c = substr(trim($s),1,-1);
            $code .='<?php if(' . $c . '){ echo "';

        }
        return $code;
    }

    private function elseifStart($str){
        $s = substr($str,0,strlen($str)-1);
        $s = substr($s,3+strlen("elseif"));  //去掉了前后标签
        //判断是否是被（）包裹起来的，如果是的话，就把整体放入if的空格里
        $code = "\";}";
        if((substr(trim($s),0,1)=="(")&&(substr(trim($s),-1)==")") ){
            $c = substr(trim($s),1,-1);
            $code .='elseif(' . $c . '){ echo "';
        }
        return $code;
    }

    private function elseStart($str){
        $s = substr($str,0,strlen($str)-1);
        $s = substr($s,3+strlen("else"));  //去掉了前后标签
        //判断是否是被（）包裹起来的，如果是的话，就把整体放入if的空格里
        $code = "\";}";
        $code .=' else{ echo "';

        return $code;
    }

    private function ifEnd($str){
        return "\";}?>";
    }
}