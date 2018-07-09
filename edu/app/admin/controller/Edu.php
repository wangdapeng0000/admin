<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Paginator;
use app\admin\model\Admin;
use think\facade\Session;

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
		Session::set('teacher_id','fsjkdlkfljsd23131sfhfj');
		$url=config("url");
        $id=session('admin_id');
        $admin= new Admin();
        $re=$admin::get($id);
        $this->username=$re;
        $this->url=$url;
    }
	
	//菜单管理
	public function class_menu(){
		$data=Db::table("edu_menu")->paginate(6);
		$page=$data->render();
    	$count = $data->total();
		$this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
		return $this->fetch();
	}
	//菜单修改
	public function menu_edit(){
		$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data["menu"]=Db::table("edu_menu")->where("id",$id)->find();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	
    	$this->assign("data",$data);
    	
        return $this->fetch();	
    }
    public function img_update(){
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	//删除之前存入的图片
    	$file = ".".Db::table('edu_menu')->where("id",$data["id"])->value('img_url');
    	if(is_file($file)){
        unlink($file);
        }
    	$add=array("menu_title"=>$data["menu_title"],"menu_img"=>$data["parent_id"],"parent_id"=>$data["parent_id"]);
    	$update=Db::table("edu_menu")->where("id",$data["id"])->update($add);
    	if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
    }
	
	//公共图片管理
	public function img(){
		$data=Db::table("edu_img")->paginate(6);
		
		$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
    	
        return $this->fetch();
	}
	
	//公共图片添加
	public function img_add(){
		return $this->fetch();
	}
    public function img_insert(){
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');

    	$add=array("class"=>$data["class"],"img_url"=>$data["img_url"]);
//    	  	dump($add);
//  	exit;
    	$insert=Db::table("edu_img")->insert($add);
    	if($insert){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
    	return $error_msg;
    }
    
    //公共图片删除
    public function img_delete(){
    $data=$this->request->param("",'','strip_tags,htmlspecialchars');
        //删除存入的图片
        $file = Db::table('edu_img')->where($data)->value('img_url');
        foreach($file as $item){
        if(is_file($item)){
        unlink(".".$item);
        }
        }
        $res=Db::table("edu_img")->where($data)->delete();
        if($res){
        	$list['code'] = 200;
            $list['error_msg'] = '删除成功!';

            
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '删除失败!';
        }
        return json($list);
    }
    
    //公共图片修改
    public function img_edit(){
    	$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data=Db::table("edu_img")->where("id",$id)->find();
    	$this->assign("data",$data);
    	
        return $this->fetch();	
    }
//  public function img_update(){
//  	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
//  	//删除之前存入的图片
//  	$file = ".".Db::table('edu_img')->where("id",$data["id"])->value('img_url');
//  	if(is_file($file)){
//      unlink($file);
//      }
//  	$add=array("class"=>$data["class"],"img_url"=>$data["img_url"]);
//  	$update=Db::table("edu_product")->where("id",$data["id"])->update($add);
//  	if($update){
//  	   $error_msg='修改成功';
//  	}else{
//  		$error_msg='修改失败';
//  	}
//  	return $error_msg;
//  }

	
	//首页页面
    public function index()
    {
      return $this->fetch();
    }
    /////////////////////////////////////
    //首页内容区
    public function shouye()
    {
    	$id=1;
//  	$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	//视频
    	$data['video']=Db::table('edu_video')->where("teacher_id",$id) ->select();
    	//关注
    	$data['attention']=Db::table('edu_teacher_student')->where("teacher_id",$id) ->select();
    	//订单余额
    	$data['order']=Db::table('edu_buy_recond')->where("teacher_id",$id) ->select();
    	//余额
    	$data['balance']=Db::table('edu_teacher_buy')->where("teacher_id",$id) ->value("balance");
    	$count['video'] = count($data['video']);
    	$count['attention'] = count($data['attention']);
    	$count['order'] = count($data['order']);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
    }
    ///////////////////////////////////////////
    //登录页
    public function login()
    {
      return $this->fetch();
    }
    //课程列表页
    public function product_list()
    {
    	$data=Db::table('edu_product') ->paginate(6);
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
    	
        return $this->fetch();
    }
    
    
 ///////////////////////////////////////////////   
   ////老师的操作
    //删除课程
    public function product_delete()
    {
        $data=$this->request->param("",'','strip_tags,htmlspecialchars');
        //删除存入的图片
        $file = Db::table('edu_product')->where($data)->column('img_url');
        foreach($file as $item){
        if(is_file($item)){
        unlink(".".$item);
        }
        }
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
    

    //修改
    public function product_edit(){
    	$pid=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$id=Session::get('teacher_id');
    	$data["product"]=Db::table("edu_product")->where("id",$pid)->find();
    	$data["sub"]=Db::table("edu_sub_class")->select();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    
    public function product_update(){
    	$id=Session::get('teacher_id');
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	//删除之前存入的图片
    	$file = ".".Db::table('edu_product')->where("edu_product.id",$data["id"])->value('img_url');
    	if(is_file($file)){
        unlink($file);
        }
    	$add=array("course_num"=>$data["course_num"],"course_info"=>$data["course_info"],"name"=>$data["name"],"teacher_id"=>$id,"courseware_num"=>$data["courseware_num"],"parent_id"=>$data["parent_id"],"sub_id"=>$data["sub_id"],"img_url"=>$data["img_url"]);
    	$update=Db::table("edu_product")->where("edu_product.id",$data["id"])->update($add);
    	if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
    }
    
     //新增课程
    public function product_add()
    {   $id=Session::get('teacher_id');
    	$data["sub"]=Db::table("edu_sub_class")->select();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	$this->assign("data",$data);
        return $this->fetch();
    }
    public function product_insert(){
    	$id=Session::get('teacher_id');
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
//  	dump($data);exit;
    	$add=array("course_num"=>$data["course_num"],"course_info"=>$data["course_info"],"name"=>$data["name"],"teacher_id"=>$id,"courseware_num"=>$data["courseware_num"],"parent_id"=>$data["parent_id"],"sub_id"=>$data["sub_id"],"img_url"=>$data["img_url"]);
    	$insert=Db::table("edu_product")->insert($add);
    	if($insert){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
    	return $error_msg;
    }
    
 /////////////////////////////////////////////////////   
    
    
     //教师列表页
    public function member_list()
    {
    	$data=Db::table("edu_teacher_info")->paginate(6);
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
    }
    
    ///////////////////////////////////////////////
    
    //老师个人中心修改
    public function member_edit(){
    	$id=Session::get('teacher_id');
    	$pid= $this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data["teacher"]=Db::table("edu_teacher_info")->where("id",$pid)->find();
    	$data["sub"]=Db::table("edu_sub_class")->select();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    public function member_update(){
    	$id=Session::get('teacher_id');
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	//删除之前存入的图片
    	$file = ".".Db::table('edu_teacher_info')->where("edu_teacher_info.id",$data["id"])->value('head_img');
    	if(is_file($file)){
        unlink($file);
        }
    	$add=array("info"=>$data["info"],"name"=>$data["name"],"tel"=>$data["tel"],"head_img"=>$data["head_img"],"parent_id"=>$data["parent_id"],"sub_id"=>$data["sub_id"],"grader"=>$data["grader"],"price"=>$data["price"]);
    	$update=Db::table("edu_teacher_info")->where("edu_teacher_info.id",$data["id"])->update($add);
    	if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
    }
   
    
    
    //////////////////////////////////////////////
    
    //教师删除
    public function member_delete()
    {
    	//获取单个或多个id
        $data= $this->request->param("",'','strip_tags,htmlspecialchars');
//	   $file_list=Db::table("edu_teacher_info")->where($data)->column("head_img");
	   $file = Db::table('edu_teacher_info')->where("id",$data["id"])->value('head_img');
    	foreach($file as $item)
        if(is_file($item)){
        unlink(".".$item);
        }
        $res=Db::table("edu_teacher_info")->where($data)->delete();
        if($res){
        	$list['code'] = 200;
            $list['error_msg'] = '删除成功!';
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '删除失败!';
        }
        return json($list);
    }
    
    //教师添加
    public function member_add(){
    	$id=Session::get('teacher_id');
    	$data["sub"]=Db::table("edu_sub_class")->select();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    public function member_insert(){
    	$id=Session::get('teacher_id');
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	
    	$add=array("name"=>$data["name"],"tel"=>$data["tel"],"grader"=>$data["grader"],"parent_id"=>$data["parent_id"],"sub_id"=>$data["sub_id"],"head_img"=>$data["head_img"],"info"=>$data["info"],"price"=>$data["price"],"teacher_id"=>$id);
    	$insert=Db::table("edu_teacher_info")->insert($add);
    	if($insert){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
    	return $error_msg;
    }
    
    
    //订单列表页
    public function order_list()
    {
//  	$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
    	$data=Db::field('edu_buy_recond.*,edu_user.*')//截取表的全部
            ->table(['edu_buy_recond'=>'edu_buy_recond','edu_user'=>'edu_user'])
            ->where('edu_buy_recond.openid=edu_user.openid')//查询条件语句
            ->paginate(6);	
    	
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
    }
    
    //财务管理
    public function buy_list(){
    	$data=Db::field('edu_teacher_buy.*,edu_teacher_info.*')//截取表的全部
            ->table(['edu_teacher_buy'=>'edu_teacher_buy','edu_teacher_info'=>'edu_teacher_info'])
            ->where('edu_teacher_buy.teacher_id=edu_teacher_info.id')//查询条件语句
            ->paginate(6);
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
    }

////////////////////////////////////////////
    //分类管理
    public function class_manager()
    {
    	$data['parent']=Db::table("edu_parent_class") ->select();//年级类
		//科目
        $data['sub']=array();
        for($i=0;$i<count($data['parent']);$i++){
            $data['sub'][$data['parent'][$i]['id']]=Db::table("edu_sub_class")->where('id',"in",$data['parent'][$i]["sub_id_list"])->select();
        }
    	$this->assign("data",$data);
    	
      return $this->fetch();
    }
//////////////////////////////////////////////////
    // 登录验证
    public function login_check()
    {

      $list['username'] = $_POST['username'];
      $list['password'] = $_POST['password'];
      $list['code'] = 200;
      $list['error_msg'] = '登录错误!';

      return json($list);
    }

//上传图片
    public function upload_img(){
	    $file=$this->request->file("file");
	    $info = $file->move('upload/edu');
	    if($info){
	    	$url="/upload/edu/".$info->getSaveName();
	    	return json_encode($url);
	    }else{
	        echo $file->getError();
	    }
	}
	
	public function insert_img(){
		$file=$this->request->file("file");
	    $info = $file->move('upload/edu');
	    if($info){
	    	$url="/upload/edu/".$info->getSaveName();
	    	return json_encode($url);
	    }else{
	    	echo $file->getError();
	    }
	}
	
	public function text(){
		$file="./new_file.text";
		//'.'是public
//      $file = $this->request->param("img_url",'','strip_tags,htmlspecialchars');
        unlink($file);
        
  

	}
	
}