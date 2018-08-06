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
    }
	
	protected $beforeActionList = [
       'gosession' =>  ['except'=>'login,login_check,aliyun_oss_callback'],    //tp前置方法，不管执行那个方法，都要先执行gosession ， 除了login,login_check方法
    ];

    //视频回调
    public function aliyun_oss_callback(){
    	$data=file_get_contents('php://input');
        $data = json_decode($data,true);
        if($data['Status'] == 'success'){
           Db::table('edu_full_video')->where('aliyun_video_id',$data['VideoId'])->update(['full_video_url'=>$data['FileUrl'],'up_time'=>time()]);
        }
    }
    
     public function gosession()
    {   
        $id=Session::get('teacher_id');
        $username=Session::get('username');
        $is_admin=Session::get('is_admin');
        if(!$username)
        {
            $this->error('请先登录','login');
        }
    }
	 
	
    
    //首页图表
    public function order_charts(){
    	$id=Session::get('teacher_id');
    	$date=Db::table("edu_teacher_income")->where("teacher_id",$id)->find();
    	
    	$data=array();
    	for($i=1;$i<=12;$i++){
    		if(strlen($i)==1){
    			array_push($data,$date['0'.$i."_order_sum"]);
    		}else{
    			array_push($data,$date[$i."_order_sum"]);
    		}
    	}
    	return json_encode($data);
    }
    
    //
    
    
    
    //推荐老师
    //列表
    public function teacher_recommend(){
    	$data=Db::field('edu_teacher_info.*,edu_teacher_tui.*')//截取表的全部
            ->table(['edu_teacher_tui'=>'edu_teacher_tui','edu_teacher_info'=>'edu_teacher_info'])
            ->where('edu_teacher_tui.teacher_id=edu_teacher_info.teacher_id')//查询条件语句
            ->select();
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    //修改
	public function teacher_recommend_edit(){
		$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data["tui"]=Db::table("edu_teacher_tui")->where("id",$id)->find();
		$data['teacher']=Db::table("edu_teacher_info")->select();
		$this->assign("data",$data);
        return $this->fetch();
	}
	public function teacher_recommend_update(){
		$data=$this->request->param("",'','strip_tags,htmlspecialchars');
		$update=Db::table("edu_teacher_tui")->where("id",$data["id"])->update($data);
		if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
	}
	//添加
	public function teacher_recommend_add(){
		$data['teacher']=Db::table("edu_teacher_info")->select();
		$this->assign("data",$data);
        return $this->fetch();
	}
	public function teacher_recommend_insert(){
		$data=$this->request->param("",'','strip_tags,htmlspecialchars');
		$insert=Db::table("edu_teacher_tui")->strict(false)->insert($data);
		if($insert){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
    	return $error_msg;
	}
	//删除
	public function teacher_recommend_delete(){
		$data=$this->request->param("",'','strip_tags,htmlspecialchars');
		$res=Db::table("edu_teacher_tui")->where($data)->delete();
        if($res){
        	$list['code'] = 200;
            $list['error_msg'] = '删除成功!';

            
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '删除失败!';
        }
        return json($list);
	}
////////////////////////////////////////////////
    //推荐课程
    //列表
    public function product_recommend(){
    	$data=Db::field('edu_product.*,edu_product_tui.*')//截取表的全部
            ->table(['edu_product_tui'=>'edu_product_tui','edu_product'=>'edu_product'])
            ->where('edu_product_tui.product_id=edu_product.id')//查询条件语句
            ->select();
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    //修改
	public function product_recommend_edit(){
		$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data["tui"]=Db::table("edu_product_tui")->where("id",$id)->find();
		$data['product']=Db::table("edu_product")->select();
		$this->assign("data",$data);
        return $this->fetch();
	}
	public function product_recommend_update(){
		$data=$this->request->param("",'','strip_tags,htmlspecialchars');
		$update=Db::table("edu_product_tui")->where("id",$data["id"])->update($data);
		if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
	}
	//添加
	public function product_recommend_add(){
		$data['product']=Db::table("edu_product")->select();
		$this->assign("data",$data);
        return $this->fetch();
	}
	public function product_recommend_insert(){
		$data=$this->request->param("",'','strip_tags,htmlspecialchars');
		$insert=Db::table("edu_product_tui")->strict(false)->insert($data);
		if($insert){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
    	return $error_msg;
	}
	//删除
	public function product_recommend_delete(){
		$data=$this->request->param("",'','strip_tags,htmlspecialchars');
		$res=Db::table("edu_product_tui")->where($data)->delete();
        if($res){
        	$list['code'] = 200;
            $list['error_msg'] = '删除成功!';

            
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '删除失败!';
        }
        return json($list);
	}
////////////////////////////////////////////////
	//用户信息
	public function user_list(){
		$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    		echo "您不是管理员,无权访问";
    	}else{
		$data=Db::table("edu_user")->paginate(6);
		$page=$data->render();
    	$count = $data->total();
		$this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
		return $this->fetch();
		}
	}
	//修改密码
	public function account_edit(){
		$id=Session::get('teacher_id');
		$data=Db::table("edu_admin")->where("teacher_id",$id)->find();
    	$this->assign("data",$data);
        return $this->fetch();	
	}
	public function account_update(){
		$data=$this->request->param("",'','strip_tags,htmlspecialchars');
		$res=Db::table("edu_admin")->where("username",$data["username"])->select();
		if(!empty($res)){
			$error_msg='用户名已存在';
		}elseif($data["password"]==$data["pass"]){
			$add=array("username"=>$data['username'],"password"=>$data["password"]);
			$update=Db::table("edu_admin")->where("teacher_id",$data["id"])->update($add);
			if($update){
    	        $error_msg='修改成功';
    	    }else{
    		    $error_msg='修改失败';
    	    }
		}else{
			$error_msg='两次输入的密码不同,请重新输入';
		}
    	return $error_msg;
	}
	//菜单管理
	public function class_menu(){
		$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    		echo "您不是管理员,无权访问";
    	}else{
		$data=Db::table("edu_menu")->paginate(6);
		$page=$data->render();
    	$count = $data->total();
		$this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
		return $this->fetch();
		}
	}
	//菜单修改
	public function menu_edit(){
		$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data["menu"]=Db::table("edu_menu")->where("id",$id)->find();
    	$data["parent"]=Db::table("edu_parent_class")->select();
    	
    	$this->assign("data",$data);
    	
        return $this->fetch();	
    }
    public function menu_update(){
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	$title=Db::table("edu_parent_class")->where("id",$data["parent_id"])->value('name');
    	//删除之前存入的图片
    	$file = ".".Db::table('edu_menu')->where("id",$data["id"])->value('menu_img');
    	if(is_file($file)){
        unlink($file);
        }
    	$add=array("menu_title"=>$title,"menu_img"=>$data["menu_img"],"parent_id"=>$data["parent_id"]);
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
		$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    		echo "您不是管理员,无权访问";
    	}else{
		$data=Db::table("edu_img")->paginate(6);
		$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
       }
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
        $file = Db::table('edu_img')->where($data)->column('img_url');
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
    public function edu_img_update(){
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	
    	//删除之前存入的图片
    	$file = ".".Db::table('edu_img')->where("id",$data["id"])->value('img_url');
    	if(is_file($file)){
        unlink($file);
        }
//  	strict自动忽略数据表不存在的字段
    	$update=Db::table("edu_img")->where("id",$data["id"])->strict(false)->update($data);
    	if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
    }
	//首页页面
    public function index()
    {
    $username=Session::get('username');
    $is_admin=Session::get('is_admin');
    $this->assign("is_admin",$is_admin);
    $this->assign("username",$username);
    return $this->fetch();
    }
    
    public function adminIndex()
    {
    $username=Session::get('username');
     $this->assign("username",$username);
      return $this->fetch();
    }
    /////////////////////////////////////
    //老师首页内容区
    public function shouye()
    {
    	$id=Session::get('teacher_id');
    	$username=Session::get('username');
    	$is_admin=Session::get('is_admin');
        //查询老师信息
        $data['teacher']=Db::table("edu_teacher_info")->where("teacher_id",$id)->find();
    	//视频
    	$data['video']=Db::table('edu_video')->where("teacher_id",$id) ->select();
    	//关注
    	$data['attention']=Db::table('edu_teacher_student')->where("teacher_id",$id) ->select();
    	//订单余额
    	$data['order']=Db::table('edu_buy_recond')->where("teacher_id",$id) ->select();
    
    	$data['time']= date('Y-m-d',time());
    	$count['video'] = count($data['video']);
    	$count['attention'] = count($data['attention']);
    	$count['order'] = count($data['order']);
    	$this->assign("username",$username);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
    }
    //老师提现
    public function teacher_buy_edit(){
    	$id=Session::get('teacher_id');
    	$res=Db::table("edu_teacher_buy")->where("teacher_id",$id)->value("balance");
    	if($res==0){
    		$error_msg='余额不足,提现失败';
    	}else{
    		$balance=$res-$res;
    		$creation_time=time();
    		$add=array("teacher_id"=>$id,"creation_time"=>$creation_time,"carry_cash"=>$res);
    		$update=Db::table("edu_teacher_buy")->where("teacher_id",$id)->update(['balance'=>$balance]);
    		$insert=Db::table("edu_withdraw_record")->insert($add);
    		$error_msg='提现成功,请等待审核通过';
    	}
    	return $error_msg;
    }
    //后台提现审核
    public function audit(){
    	$data=Db::field('edu_teacher_info.*,edu_withdraw_record.*')//截取表的全部
            ->table(['edu_teacher_info'=>'edu_teacher_info','edu_withdraw_record'=>'edu_withdraw_record'])
            ->where('edu_withdraw_record.teacher_id=edu_teacher_info.teacher_id')//查询条件语句
            ->where("carry_status",0)
            ->paginate(6);
		$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
    	return $this->fetch();
    }
    public function check(){
    	$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$update=Db::table('edu_withdraw_record')->where("id",$id)->update(['carry_status' => 1]);
    	if($update){
        	$list['code'] = 200;
            $list['error_msg'] = '删除成功!';

            
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '删除失败!';
        }
        return json($list);
    }
    //老师提现记录统计
    public function teacher_carry_statistics(){
    	$data=Db::field('edu_teacher_info.*,edu_withdraw_record.*')//截取表的全部
            ->table(['edu_teacher_info'=>'edu_teacher_info','edu_withdraw_record'=>'edu_withdraw_record'])
            ->where('edu_withdraw_record.teacher_id=edu_teacher_info.teacher_id')//查询条件语句
            ->paginate(6);
    	
		$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
    	return $this->fetch();
    }
    //管理员首页内容区
    public function homepage(){
    	$username=Session::get('username');
        //查询老师信息
        $data['teacher']=Db::table("edu_teacher_info")->select();
    	//视频
    	$data['video']=Db::table('edu_video') ->select();
    	//订单总数
    	$data['order']=Db::table('edu_buy_recond') ->select();
    	//用户总数
    	$data["user"]=Db::table('edu_user')->select();
    	$data['time']= date('Y-m-d',time());
    	$count['teacher'] = count($data['teacher']);
    	$count['video'] = count($data['video']);
    	$count['order'] = count($data['order']);
    	$count['user'] = count($data['user']);
        $this->assign("count",$count);
        $this->assign("data",$data);
        return $this->fetch();
    }
    ///////////////////////////////////////////
    //登录页
    public function login()
    {
      Session::clear();
      return $this->fetch();
    }
    // 登录验证
    public function login_check(){
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	if(!empty($data['username'])&&!empty($data['password'])){
    		$res =Db::table('edu_admin')->where($data)->find();
    		if(empty($res)){
    			
    			$list['error_msg']='登录失败,用户名或密码错误';
    		}else{
    			Session::set('username',$res['username']);
    			Session::set('is_admin',$res['is_admin']);
    			Session::set('teacher_id',$res['teacher_id']);
    			    $list['code'] = 200;
    			$list['error_msg']='登录成功';
    		}
    	}
    	return json($list);
    }
    //退出登录
    ///////////////////////////////////////////////////////////////////////////
    
//   管理员
    //课程列表页
    public function product_list()
    {
    	$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    		echo "您不是管理员,无权访问";
    	}else{
    	$data=Db::table('edu_product') ->paginate(6);
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
    	
        return $this->fetch();
        }
    }
  ////////////////////////////////////////////////////////////////////////////////////////////  
    //老师课程信息页
    public function teaproduct_list(){
    	$teacher_id=Session::get('teacher_id');
    	$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    	$data['product']=Db::table('edu_product') ->where("teacher_id",$teacher_id)->find();
    	$data['video']=Db::table('edu_video video, edu_full_video full')
                   ->where('video.video_id = full.video_id')
                   ->field('video.*,full.aliyun_video_id,full.full_video_url,full.up_time')
                   ->where("video.teacher_id",$teacher_id)
                   ->paginate(6);
    	$page=$data['video']->render();
    	$count = $data['video']->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
    	return $this->fetch();
    	}else{
    		echo "缺少相关参数,无法访问";
    	}
    }
    
    public function teaproduct_video(){
    	$vid=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data=Db::table("edu_video")->where("id",$vid)->find();
    	$time=Db::table("edu_full_video")->where("video_id",$data["video_id"])->value("up_time");
    	
        $oss = new Oss(); 
        $oss_auth = $oss->aliyun_upload_video($data);
        $this->assign("UploadAddress",$oss_auth->UploadAddress);
        $this->assign("VideoId",$oss_auth->VideoId);
        $this->assign("RequestId",$oss_auth->RequestId);
        $this->assign("UploadAuth",$oss_auth->UploadAuth);
    	$this->assign("data",$data);
    	return $this->fetch();	
    	
    }

    public function del_aliyun_video()
    {
        $videoId=$this->request->param("videoId",'','strip_tags,htmlspecialchars');
        $oss = new Oss();
        $oss->delete_videos($videoId);  
    }


    
    public function teaproduct_edit(){
    	$id=Session::get('teacher_id');
    	$vid=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data=Db::table("edu_video")->where("teacher_id",$id)->where("id",$vid)->find();
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    public function teaproduct_update(){
    	$id=Session::get('teacher_id');
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	$add=array("video_name"=>$data["video_name"],"courseware_num"=>$data["courseware_num"],"video_img_url"=>$data["video_img_url"]);
    	$update=Db::table("edu_video")->where("teacher_id",$id)->update($add);
    	if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
    }
    
    //老师视频添加页
    public function teaproduct_video_add(){
        $data=$this->request->param("",'','strip_tags,htmlspecialchars');
        if ($data['video_id']&&$data['videoId']) {
            Db::table("edu_full_video")->where('video_id',$data['video_id'])->update(['aliyun_video_id'=>$data['videoId']]);
        } 
        return json_encode(['err_code'=>200,'err_msg'=>'上传成功']);
    }

    public function teaproduct_add(){
    	return $this->fetch();
    }


    

    
    public function teaproduct_insert(){
    	$video_id=$this->getNonceStr();
    	$id=Session::get('teacher_id');
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	$add=array("video_name"=>$data["name"],"teacher_id"=>$id,"courseware_num"=>$data["courseware_num"],"video_img_url"=>$data["video_img_url"],"video_id"=>$video_id);
    	$insert=Db::table("edu_video")->insert($add);
    	$full=array("video_id"=>$video_id,"teacher_id"=>$id);
    	$inserts=Db::table("edu_full_video")->insert($full);
    	if($inserts){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
    	return $error_msg;
    }
    
     public function teaproduct_delete()
    {
        $data=$this->request->param("",'','strip_tags,htmlspecialchars');
        //删除存入的图片
        $file = Db::table('edu_video')->where($data)->column('video_img_url');
        foreach($file as $item){
        if(is_file($item)){
        unlink(".".$item);
        }
        }
        $res=Db::table("edu_video")->where($data)->delete();
        if($res){
        	$list['code'] = 200;
            $list['error_msg'] = '删除成功!';

            
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '删除失败!';
        }
        return json($list); 
    }

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
    
    
 ///////////////////////////////////////////////   

   
   
   //视频上传
   
    //修改课程信息
    public function product_edit(){
    	$pid=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data['product']=Db::table("edu_product")->where("id",$pid)->find();
    	
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
    	$add=array("course_num"=>$data["course_num"],"course_info"=>$data["course_info"],"name"=>$data["name"],"teacher_id"=>$id,"courseware_num"=>$data["courseware_num"],"img_url"=>$data["img_url"]);
    	$update=Db::table("edu_product")->where("edu_product.id",$data["id"])->update($add);
    	if($update){
    	   $error_msg='修改成功';
    	}else{
    		$error_msg='修改失败';
    	}
    	return $error_msg;
    }
    
    
 /////////////////////////////////////////////////////       
     //新增课程
    public function product_add()
    {   
    	$id=Session::get('teacher_id');
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
    

    
    
     //教师列表页
    public function member_list()
    {
    	$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    		echo "您不是管理员,无权访问";
    	}else{
    	$data=Db::table("edu_teacher_info")->paginate(6);
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
       }
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
    	$file = ".".Db::table('edu_teacher_info')->where("id",$data["id"])->value('head_img');
    	if(is_file($file)){
        unlink($file);
        }
    	$add=array("info"=>$data["info"],"name"=>$data["name"],"head_img"=>$data["head_img"]);
    	$update=Db::table("edu_teacher_info")->where("id",$data["id"])->update($add);
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
	   $file=Db::table("edu_teacher_info")->where($data)->column("head_img");
    	foreach($file as $item)
        if(is_file($item)){
        unlink(".".$item);
        }
        $res=Db::table("edu_teacher_info")->where($data)->delete();
        if($res){
        	$list['code'] = 200;
            $list['error_msg'] = '成功!';
        }else{
        	$list['code'] = 000;
            $list['error_msg'] = '失败!';
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
    ////////////////////////////////////////////////////////
    public function member_insert(){
    	$id=$this->getNonceStr();
    	$teapassword=$this->teaPassword();
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	$res=Db::table("edu_admin")->where("phone",$data['tel'])->find();
    	if(empty($res)){
    	//从获取	页面获取的要添加的老师的基本信息
    	$data["time"]=time();
    	$add=array("name"=>$data["name"],"tel"=>$data["tel"],"grader"=>$data["grader"],"parent_id"=>$data["parent_id"],"sub_id"=>$data["sub_id"],"head_img"=>$data["head_img"],"info"=>$data["info"],"price"=>$data["price"],"teacher_id"=>$id,"reg_time"=>$data["time"]);
    	//添加老师后台登录的账号和密码
    	$eduadmin=array("phone"=>$data["tel"],"teacher_id"=>$id,"password"=>$teapassword);
    	//默认添加老师的课程
    	$product=array("teacher_id"=>$id,"img_url"=>$data["head_img"],"name"=>$data["name"]);
    	
    	$product=Db::table("edu_product")->strict(false)->insert($product);
    	$admin=Db::table("edu_admin")->insert($eduadmin);
    	$insert=Db::table("edu_teacher_info")->insert($add);
    	if($insert){
    	   $error_msg='添加成功';
    	}else{
    		$error_msg='添加失败';
    	}
    	}else{
    		$error_msg='用户已存在';
    	}
    	return $error_msg;
    }
    
    
    //订单列表页
    public function order_list()
    {
    	$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    		$teacher_id=Session::get('teacher_id');
    		$data=Db::field('edu_order.*,edu_user.*')//截取表的全部
            ->table(['edu_order'=>'edu_order','edu_user'=>'edu_user'])
            ->where('edu_order.openid=edu_user.openid')//查询条件语句
            ->where('teacher_id',$teacher_id)
            ->paginate(6);	
    	}else{
    	$data=Db::field('edu_order.*,edu_user.*')//截取表的全部
            ->table(['edu_order'=>'edu_order','edu_user'=>'edu_user'])
            ->where('edu_order.openid=edu_user.openid')//查询条件语句
            ->paginate(6);	
        }
    	$page=$data->render();
    	$count = $data->total();
        $this->assign("page",$page);
    	$this->assign("data",$data);
    	$this->assign("count",$count);
        return $this->fetch();
    }
    
    //财务管理
    public function buy_list(){
    	$is_admin=Session::get('is_admin');
    	if($is_admin==0){
    		echo "您不是管理员,无权访问";
    	}else{
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
//      dump($data);
//      exit;
        
    	$this->assign("data",$data);
    	
      return $this->fetch();
    }
//////////////////////////////////////////////////
    //添加子类
    public function class_add(){
    	$id=$this->request->param("id",'','strip_tags,htmlspecialchars');
    	$data=Db::table("edu_parent_class")->where("id",$id)->value("name");
    	
    	$this->assign("data",$data);
    	return $this->fetch();
    }
    public function class_insert(){
    	$data=$this->request->param("",'','strip_tags,htmlspecialchars');
    	$sub_name=Db::table("edu_sub_class")->where("name",$data["name"])->find();
    	if(!$sub_name){
    		$sub_id=Db::table("edu_sub_class")->insertGetId(["name"=>$data["name"]]);
    	}else{
    	    $sub_id=$sub_name["id"];
    	}
    	$parent=Db::table("edu_parent_class")->where("name",$data["parent_name"])->value("sub_id_list");
    	$sub=Db::table("edu_sub_class")->where('id',"in",$parent)->column("name");
    	if (in_array($data["name"], $sub)){
    		$error_msg='该科目已存在';
       }else{
    	    $data["sub_name"]=$data["name"];
        	$data["parent_id"]=Db::table("edu_parent_class")->where("name",$data["parent_name"])->value("id");
        	$data["status"]="add";
        	$record=$this->change_summary($data);
        	$parent=$parent.",".$sub_id;
        	$parent_sub=Db::table("edu_parent_class")->where("name",$data["parent_name"])->update(["sub_id_list"=>$parent]);
      	    $error_msg='添加科目成功';
        }
    	return $error_msg;
    }
    
    //删除子类
    public function class_delete(){
    	$data= $this->request->param("",'','strip_tags,htmlspecialchars');
    	$data["sub_name"]=Db::table("edu_sub_class")->where("id",$data["sub_id"])->value("name");
    	$teacher=Db::table("edu_teacher_info")
    	    ->where("sub_id",$data["sub_id"])
    	    ->where("parent_id",$data["parent_id"])
    	    ->select();
    	if(!$teacher){
    		$data["status"]="delete";
    		$record=$this->change_summary($data);
    		$parent=Db::table("edu_parent_class")->where("id",$data["parent_id"])->value("sub_id_list");
            $findLen = strlen($data['sub_id']);
            $tmp = stripos($parent, $data['sub_id']);
            if(strlen($parent)==1){
            	$list['code'] = 400;
                $list['error_msg'] = '该科目不能再删了!';
            }else{
                if($tmp==0){
            	    $sub_id_list=str_replace($data["sub_id"].",",'',$parent);
                }else{
                    $sub_id_list=str_replace(",".$data["sub_id"],'',$parent);
                }
                $parent_update=Db::table("edu_parent_class")->where("id",$data["parent_id"])->update(["sub_id_list"=>$sub_id_list]);
                if($parent_update){
        	        $list['code'] = 200;
                    $list['error_msg'] = '成功!';
                }else{
        	        $list['code'] = 000;
                    $list['error_msg'] = '失败!';
                }
            }
    	}else{
    		$list['code'] = 400;
            $list['error_msg'] = '该科目下还有老师,不能删除!';
    	}
        return json($list);
    }
    
    //科目更改记录表
    public function change_summary($data){
    	$name=Session::get('username');
    	$insert=array("sub_name"=>$data["sub_name"],"parent_id"=>$data["parent_id"],"admin_name"=>$name,"change_status"=>$data["status"]);
    	Db::table("edu_class_change_summary")->insert($insert);
    }
    
    

//上传图片
    public function upload_img(){
	    $file=$this->request->file("file");
	    $info = $file->move('upload/edu');
	    if($info){
	        $img_url = $info->getSaveName();
            $img_url= str_replace("\\","/",$img_url);
	    	$url="/upload/edu/".$img_url;
	    	
	    	return json_encode($url);
	    }else{
	        echo $file->getError();
	    }
	}
	
	public function insert_img(){
		$file=$this->request->file("file");
	    $info = $file->move('upload/edu');
	    if($info){
	    	$img_url = $info->getSaveName();
            $img_url= str_replace("\\","/",$img_url);
	    	$url="/upload/edu/".$img_url;
	    	
	    	return json_encode($url);
	    }else{
	    	echo $file->getError();
	    }
	}
	
	//课程视频查询页
	public function teacher_product_list(){
//	    $teacher_id="fsjkdlkfljsd23131sfhfj";
//	    $teacher_id=$this->request->param("teacher_id",'','strip_tags,htmlspecialchars');
//	    
//	    $data=Db::table("edu_video")->where("teacher_id",$teacher_id)->paginate(6);
//	    $page=$data->render();
//      $count = $data->total();
//      $this->assign("page",$page);
//      $this->assign("data",$data);
//      $this->assign("count",$count);
        return $this->fetch();
	    
	}

    // 课程视频管理页
    public function product_video_edit()
    {    
        $data=Db::table('edu_product') ->paginate(6);
        dump($data);
        exit();
        $page=$data->render();
        $count = $data->total();
        $this->assign("page",$page);
        $this->assign("data",$data);
        $this->assign("count",$count);
        return $this->fetch();
    }

    //模拟请求get，param  
      public function http_curl($url, $data = null)
      {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_Param, 1);
            curl_setopt($curl, CURLOPT_ParamFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
      }//curl
	
    
	public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    
    public function teaPassword($length = 12)
    {
        $chars = "0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
   


  

}