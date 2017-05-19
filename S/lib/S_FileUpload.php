<?php
/**
 * Created by PhpStorm.
 * User: silsuer
 * Date: 2017/5/19
 * Time: 10:39
 * 文件上传类
 */

class S_FileUpload{
    private $path; //文件上传路径//默认是配置文件中放置的
    private $maxSize = 3000000;//上传文件的最大文件大小
    private $allowType = 'jpg,sql' ;//允许上传的文件的后缀名
    private $renameType = 'time';  //上传后重命名文件的方式，有md5（md5加密后保存），time（按照当前时间戳保存），或者自定义命名方法
//    private $isUploadAllFile = true; //是否允许上传所有类型的文件

    private $fileNum=1; //上传的文件数量，默认是1
    private $originName; //源文件名
    private $tempName; //临时文件名
    private $ext;  //文件后缀
    private $fileSize; //文件大小
    private $fileName; //新文件名
    private $errorNum; //错误代码
    private $errorMsg; //错误报告消息

    public function getInfo(){
        $vv = array(
            'path'=>$this->path,
            'maxSize'=>$this->maxSize,
            'allowType'=>$this->allowType,
            'rename'=>$this->renameType,
            'originName'=>$this->originName,
            'tempName'=>$this->tempName,
            'ext'=>$this->ext,
            'fileSize'=>$this->fileSize,
            'fileName'=>$this->fileName
        );
        return $vv;
    }

    /**
     * 用于设置成员属性（$path, $allowtype,$maxsize, $israndname）
     * 可以通过连贯操作一次设置多个属性值
     *@param  string $key  成员属性名(不区分大小写)
     *@param  mixed  $val  为成员属性设置的值,对于allowType，采用字符串的形式赋值，用逗号分割
     *@return  object     返回自己对象$this，可以用于连贯操作
     */
    public function set($key,$val=null){

        if (is_array($key) && is_null($val)){  //如果传进来的是一个数组，那么遍历数组进行赋值
            foreach ($key as $k=>$v){
                $k = strtolower($k);
                if( array_key_exists( $k, get_class_vars(get_class($this) ) ) ){
                    $this->setOption($k, $v);
                }
            }
        }else{
            $key = strtolower($key);
            if( array_key_exists( $key, get_class_vars(get_class($this) ) ) ){
                $this->setOption($key, $val);
            }
        }
      return $this;
    }

    private function setOption($key, $val) { //为单个成员赋值
        $this->$key = $val;
    }

    /**
     * 调用该方法上传文件
     * @param  string $fileFile  上传文件的表单名称
     * @return bool        如果上传成功返回数true
     */

    function upload($fileField) {
        $return = true;
        /* 检查文件路径是否合法 */
        if( !$this->checkFilePath() ) {
            $this->errorMsg = $this->getError();
            return false;
        }
        /* 将文件上传的信息取出赋给变量 */
        //dump($_FILES[$fileField]);
        $name = $_FILES[$fileField]['name']; //文件名，多文件上传回事个数组
        $tmp_name = $_FILES[$fileField]['tmp_name']; //临时文件名
        $size = $_FILES[$fileField]['size'];//文件大小
        $error = $_FILES[$fileField]['error'];//文件错误
        $this->fileNum = count($name);//设定本次上传文件的数量
        /* 如果是多个文件上传则$file["name"]会是一个数组 */
        if(is_Array($name)){
            $infos = array(); //这是上传多个文件时要返回的数组，返回所有上传的文件信息
            $errors=array();//先给错误赋值一个空数组
            /*多个文件上传则循环处理 ， 这个循环只有检查上传文件的作用，并没有真正上传 */
            for($i = 0; $i < count($name); $i++){
                /*设置文件信息 */
                if($this->setFiles($name[$i],$tmp_name[$i],$size[$i],$error[$i] )) {
                    if(!$this->checkFileSize() || !$this->checkFileType()){
                        $errors[] = $this->getError();
                        $return=false;
                    }
                }else{
                    $errors[] = $this->getError();
                    $return=false;
                }
                /* 如果有问题，则重新初使化属性 */
                if(!$return)
                    $this->setFiles();
            }

            if($return){
                /* 存放所有上传后文件名的变量数组 */
                $fileNames = array();
                /* 如果上传的多个文件都是合法的，则通过销魂循环向服务器上传文件 */
                for($i = 0; $i < count($name); $i++){
                    if($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i] )) {
                        $this->setNewFileName();
                        if(!$this->copyFile()){
                            $errors[] = $this->getError();
                            $return = false;
                        }
                        $fileNames[] = $this->fileName;
                        $infos[] = $this->getInfo();
                    }
                }
                $this->fileName = $fileNames;

            }
            $this->errorMsg = $errors;
            if (!$return){  //上传失败
                dump($this->errorMsg);
                return false;
            }else{
                return $infos;
            }
            /*上传单个文件处理方法*/
        } else {
            /* 设置文件信息 */
            $info=[]; //这里是要返回的文件信息数组
            if($this->setFiles($name,$tmp_name,$size,$error)) {

                /* 上传之前先检查一下大小和类型 */
                if($this->checkFileSize() && $this->checkFileType()){
                    /* 为上传文件设置新文件名 */

                    $this->setNewFileName();

                    /* 上传文件  返回0为成功， 小于0都为错误 */

                    if($this->copyFile()){
                        $info = $this->getInfo();
                        return $info;
                    }else{
                        $return=false;
                    }
                }else{
                    $return=false;
                }
            } else {
                $return=false;
            }
            //如果$return为false, 则出错，将错误信息保存在属性errorMess中
            if(!$return){
                $this->errorMsg = $this->getError();
            }else{
                return $info;
            }

            return $return;
        }
    }

    /* 检查上传的文件是否是合法的类型 */
    private function checkFileType() {

        if ($this->allowType==''){

            return true;    //如果允许上传的类型是空的话，那么就允许所有类型的文件上传
        }else{
            $type = explode(',',$this->allowType); //把允许上传的文件类型按逗号分割成数组
            if (in_array($this->ext,$type)){
                return true;
            }else{
                $this->setOption('errorNum', -1);  //如果文件类型不合法，就设置错误编号是-1，并返回false
                return false;
            }
        }

    }

    /* 检查是否有存放上传文件的目录 */
    private function checkFilePath() {
        if(empty($this->path)){
           $this->path = C('upload_path');
        }
        if (!file_exists($this->path) || !is_writable($this->path)) {

            if (!mkdir($this->path, 0755)) {
                $this->setOption('errorNum', -4);
                return false;
            }
        }

        return true;
    }

    /* 设置上传出错信息 */
    private function getError() {
        $str = "上传文件<span color='red'>{$this->originName}</span>时出错 : ";
        switch ($this->errorNum) {
            case 4: $str .= "没有文件被上传"; break;
            case 3: $str .= "文件只有部分被上传"; break;
            case 2: $str .= "上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值"; break;
            case 1: $str .= "上传的文件超过了php.ini中upload_max_filesize选项限制的值"; break;
            case -1: $str .= "未允许类型"; break;
            case -2: $str .= "文件过大,上传的文件不能超过{$this->maxsize}个字节"; break;
            case -3: $str .= "上传失败"; break;
            case -4: $str .= "建立存放上传文件目录失败，请重新指定上传目录"; break;
            case -5: $str .= "必须指定上传文件的路径"; break;
            default: $str .= "未知错误";
        }
        return $str.'<br>';
    }

    /* 设置和$_FILES有关的内容 */
    private function setFiles($name="", $tmp_name="", $size=0, $error=0) {
        $this->setOption('errorNum', $error); //设定错误代码
        if($error){
            return false;
        }
        $this->setOption('originName', $name);
        $this->setOption('tempName',$tmp_name);
        $aryStr = explode(".", $name);
        $this->setOption('ext', strtolower($aryStr[count($aryStr)-1]));
        $this->setOption('fileSize', $size);
        return true;
    }

    /* 检查上传的文件是否是允许的大小 */
    private function checkFileSize() {
        if ($this->fileSize > $this->maxSize) {
            $this->setOption('errorNum', -2);
            return false;
        }else{
            return true;
        }
    }


    /* 设置上传后的文件名称 根据重命名方式命名新的文件名，也可以根据自己去实现命名方法 */
    private function setNewFileName() {
        $name="";
        switch ($this->renameType){
            case 'md5':
                $name=md5($this->originName);
                break;
            case 'time':
                $name=time();
                break;
            default:
                $name=$this->originName;
                break;
        }
        if($this->fileNum!=1){//如果不止上传了一个文件的话，就使用随机函数
            $name .='_'.rand(0,$this->fileNum*1000);
        }
        $this->setOption('fileName',$name);

    }

    /* 复制上传文件到指定的位置 */
    private function copyFile() {

        if(!$this->errorNum) {
            $path = rtrim($this->path, '/').'/';
            $path .= $this->fileName .'.'. $this->ext;     //拼接出当前的目录
//            echo $path."a
            if (move_uploaded_file($this->tempName, $path)) {

                return true;
            }else{
                $this->setOption('errorNum', -3);
                echo 222;
                return false;
            }
        } else {
            return false;
        }
    }

    private function showErr($error,$time=5){
        //展示上传完成情况

    }
}