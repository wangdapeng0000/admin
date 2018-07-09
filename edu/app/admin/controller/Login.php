<?php
namespace  app\admin\controller;
use  think\Controller;
use think\Request;
use app\admin\model\Admin;
header('Access-Control-Allow-Origin:*');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT');
class Login extends Controller {
	protected $request;
	
	public function __construct(Request $request)
    {
		$this->request = $request;
		parent::__construct();
    }

      public function login(){

        return  $this->fetch();


      }

     public function loginRun(Request $request){
          $re=$this->request->param("",'','strip_tags,htmlspecialchars');
          $url=config('url');
          $username=$re["username"];
          $password=$re["password"];
//          echo $username;
          $admin= new  Admin();
          $re=$admin->where("username","=",$username)->find();
          if(empty($re)){
          	$list['code'] = 100;
            $list['error_msg'] = '用户名不存在!';
//        	$error_msg='用户名不存在';
//            $this->error("用户名不存在",$url["user"]["login"]);

          }elseif($re["password"]!=$password){
          	$list['code'] = 000;
            $list['error_msg'] = '密码错误!';
//        	$error_msg='密码错误';
//
//
//            $this->error("密码错误",$url["user"]["login"],'',1);

          }else{
          session("username",$username);
          session("password",$password);
          session("admin_id",$re['admin_id']);
          $list['code'] = 200;
          $list['error_msg'] = '登录成功!';
//        $error_msg='登录成功';
//        $this->success("登录成功",$url["admin"]["main"],'',1);
          }
           return json($list);
     }

     public function logout(){
       $url=config('url');
       session("username",null);
       session("admin_id",null);
       if(empty(session('?username'))){
           $this->success("注销成功",$url["user"]["login"],'',1);
       }


     }



}



?>