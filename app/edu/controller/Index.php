<?php
namespace  app\edu\controller;
use app\edu\model\Admin;
use think\Controller;
use think\Request;

class Index extends Controller {
    private $username;
    

    
    public function index(){
    	$this->assign([
//        "url"=>$this->url,
          "username"=>$this->username
      ]);
    	return  $this->fetch();
    }
    
    public function welcome(){
    	return  $this->fetch();
    }
    
}

