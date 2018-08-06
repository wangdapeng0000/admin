<?php
namespace app\index\controller;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
    	$this->redirect("admin/edu/login");
//  	echo "aaa";
//      header("Location: http://www.baidu.com");
    }
}
 