<?php
namespace app\admin\controller;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $this->redirect("admin/edu/login");
//      return $this->fetch();
    }

}
