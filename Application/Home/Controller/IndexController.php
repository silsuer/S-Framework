<?php
namespace Home;
use S\Controller;

class IndexController extends Controller {
  public  function  index() {
      $data = array("asd","dfg","sdfsdf");
      $this->data = $data;
      //dump($data);
      $this->display("index.html");
  }
}