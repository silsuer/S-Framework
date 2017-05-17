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
}