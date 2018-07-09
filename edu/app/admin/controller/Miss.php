<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/22
 * Time: 19:11
 */
namespace app\admin\controller;
use think\Controller;
header('Access-Control-Allow-Origin:*');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT');

class Miss extends Controller{
    public function run(){
        $url = config("url");
        if(session("?username")){
            $this->error("您要访问的页面不存在",$url["admin"]["main"]);
        }else{
            $this->error("您要访问的页面不存在",$url["user"]["login"]);
        }
    }
}
?>