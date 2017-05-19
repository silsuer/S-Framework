<?php
namespace Home;
use S\Controller;

class IndexController extends Controller {
  public  function  index() {
      $data = array("asd","dfg","sdfsdf");
      $this->data = $data;
      $this->d = 3;
   
      $this->display("index.html");
  }

  public function dd(){
      $this->a = I('id');
      $this->b = I('se');
      $this->display('select.html');
  }

  public function ddaction(){
      echo "这里是文件上传下载类处理页面";
     // dump($_FILES['photo']);
      lib('S_FileUpload'); //引入上传类
      $f = new \S_FileUpload();  //new上传类
      //$f->set('path','asdalksdjlajsd');
//      $f->checkFilePath();
      $a =  $f->upload('photo');
      dump($f->getInfo());
      dump($a);
  }
}