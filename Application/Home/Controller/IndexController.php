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
      error('填写失败');
      //$this->display('select.html');
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

  public function page(){
dump($_GET);
      lib('S_Page');
//      dump($_SERVER['PHP_SELF']);die();
      $p = new \S_Page();
      $a = $p->page(5000,15,$_SERVER['PHP_SELF'],'page');
      $b = $p->make_page_with_points(5000,80,$_SERVER['PHP_SELF'],15,2,'page');
      dump($b);
  }
}