<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Paginator;

/*
 *  教育小程序 
 */
class Edu extends Controller
{
	protected $request;
	
	public function __construct(Request $request)
    {
		$this->request = $request;
		parent::__construct();
    }
	
	
	//首页页面
    public function index()
    {
      return $this->fetch();
    }
    //首页内容区
    public function shouye()
    {
      return $this->fetch();
    }
    //登录页
    public function login()
    {
      return $this->fetch();
    }
    //课程列表页
    public function product_list()
    {
//  	$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
    	
    	$data=Db::table('edu_product') ->paginate(6);
    	
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
    	
        return $this->fetch();
    }
    
    //删除课程
    public function product_delete()
    {
        $data=$this->request->param("",'','strip_tags,htmlspecialchars');
        $res=Db::table("edu_product")->where($data)->delete();
        if($res){
        	$list['code'] = 200;
            $list['error_msg'] = '删除成功!';

            
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '删除失败!';
        }
        return json($list);
    }
    

    
    public function product_edit(){
    	$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data["product"]=Db::table("edu_product")->where("id",$id)->find();
    	$data["sub"]=Db::table("edu_sub_class")->select();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    //修改
    public function product_update(){
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
//  	dump($data);
//  	exit;
    	$add=array("name"=>$data["name"],"courseware_num"=>$data["courseware_num"],"parent_id"=>$data["parent_id"],"sub_id"=>$data["sub_id"],"img_url"=>$data["img_url"]);
//  	dump($data);
//  	exit;
    	$update=Db::table("edu_product")->where("edu_product.id",$data["id"])->update($add);
    	if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
//  	dump($data);
    	return $error_msg;
    }
    
     //新增课程
    public function product_add()
    {
    	$data["sub"]=Db::table("edu_sub_class")->select();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	$this->assign("data",$data);
    	
        return $this->fetch();
    }
    public function product_insert(){
    	
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
//  	dump($data);
//  	exit;
    	$add=array("name"=>$data["name"],"teacher_id"=>$data["teacher_id"],"full_video_url"=>$data["full_video_url"],"vidio_size"=>$data["vidio_size"],"courseware_num"=>$data["courseware_num"],"parent_id"=>$data["parent_id"],"sub_id"=>$data["sub_id"],"img_url"=>$data["img_url"]);
    	$insert=Db::table("edu_product")->insert($add);
    	if($insert){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
//  	dump($data);
    	return $error_msg;
    }
    
     //会员列表页
    public function member_list()
    {
      return $this->fetch();
    }
    //订单列表页
    public function order_list()
    {
      return $this->fetch();
    }

    //分类管理
    public function class_manager()
    {
      return $this->fetch();
    }

    // 登录验证
    public function login_check()
    {

      $list['username'] = $_POST['username'];
      $list['password'] = $_POST['password'];
      $list['code'] = 200;
      $list['error_msg'] = '登录错误!';

      return json($list);
    }

    public function upload_img(){
//  	$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
	    // 获取表单上传文件 例如上传了001.jpg
	    $file=$this->request->file("file",'','strip_tags,htmlspecialchars');
//	    $file = request()->file('file');
	    // 移动到框架应用根目录/uploads/ 目录下
	    $info = $file->move( '../public/upload/edu');
	    if($info){
//	    	dump($info);
//	    	dump($id);
	    	$url="/public/upload/edu/".$info->getSaveName();
	    	return json_encode($url);
//	    	$res=Db::table("edu_product")->where("id",$id)->update(["img_url"=>$url]);
//	    	if($res){
//	    		$error_msg='修改成功';
//  	    }else{
//  		    $error_msg='修改失败';
//  	    }
//	    	return $res;
	    }else{
	        // 上传失败获取错误信息
	        echo $file->getError();
	    }
	}
	
	public function insert_img(){
		$file=$this->request->file("file",'','strip_tags,htmlspecialchars');
	    // 移动到框架应用根目录/uploads/ 目录下
	    $info = $file->move( '../public/upload/edu');
	    if($info){
	    	$url="/public/upload/edu/".$info->getSaveName();
	    	return json_encode($url);
//	    	dump($url);
//	    	exit;
	    }else{
	    	echo $file->getError();
	    }
	}
	
}