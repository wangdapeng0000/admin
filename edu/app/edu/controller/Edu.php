<?php
namespace app\edu\controller;
use \think\Controller;
use \think\Request;
use \think\Db;

class Edu extends Controller
{
	
	protected $request;
	
	public function __construct(Request $request)
    {
		$this->request = $request;
    }
    
    //视频会员是否过期
    public function expire(){
    	$openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
		$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
		$current=time();
		$data=Db::table("edu_buy_recond")
		    ->where("teacher_id",$teacher_id)
		    ->where("openid",$openid)
		    ->select();
		if(empty($data)){//未购买
			return json_encode(["code"=>1002,"msg"=>"您无权观看"]);
		}
		$time=$data[0]["expire_time"];
		if($current<$time){//未过期
			return json_encode(["code"=>1000,"msg"=>"您的订单还有时间"]);
		}else{//已过期
			return json_encode(["code"=>1001,"msg"=>"您的订单已过期"]);
		}
		
    }
	
	//首页分类
	public function parent_class(){

		$data['parent']=Db::table("edu_parent_class") ->select();//年级类
		//科目
        $data['sub']=array();
        for($i=0;$i<count($data['parent']);$i++){
            $data['sub'][$data['parent'][$i]['id']]=Db::table("edu_sub_class")->where('id',"in",$data['parent'][$i]["sub_id_list"])->select();
        }
        //老师
        $data['teacher_parent']=array();
        for($i=1;$i<=count($data['parent']);$i++){
            $data['teacher_parent'][$i]=Db::table("edu_teacher_info")->where('parent_id',$i)->select();
        }
		return json_encode($data['sub']);
	}
	
    //首页
	public function index(){
		$data["img"]=Db::table("edu_img")->where("class","index") ->select();//公共图
		$data["menu"]=Db::table("edu_menu") ->select();//菜单
		//推荐视频
		$data["course"]=Db::field('edu_product_tui.*,edu_product.*')//截取表的全部
            ->table(['edu_product_tui'=>'edu_product_tui','edu_product'=>'edu_product'])
            ->where('edu_product_tui.teacher_id=edu_product.teacher_id')//查询条件语句
            ->select();	
        //推荐老师
		$data["teacher"]=Db::field('edu_teacher_tui.*,edu_teacher_info.*')//截取表的全部
            ->table(['edu_teacher_tui'=>'edu_teacher_tui','edu_teacher_info'=>'edu_teacher_info'])
            ->where('edu_teacher_tui.teacher_id=edu_teacher_info.teacher_id')//查询条件语句
            ->select();
		return json_encode($data);
	}
	
	//授课老师
//	public function teacher(){     //id为空时,输出 {"id":"1","name":"adsad"}       
//		$id=$this->request->post("id",'','strip_tags,htmlspecialchars');//获取id并过滤
////	return json_encode($id);  输出{"id":"1"}  
//		if(empty($id)){
//			return json_encode(["code"=>1001,"msg"=>"无参数"]);
//		}else{
//			$data=Db::table('edu_teacher_info')->where("id",$id) ->select();
//			if(empty($data)){
//				return json_encode(["code"=>1002,"msg"=>"参数错误"]);
//			}else{
//		        return json_encode($data);
//			}
//		}
//	}
	
	//名师推荐
	public function teacher_tui(){
		$data=Db::field('edu_teacher_tui.*,edu_teacher_info.*')//截取表的全部
            ->table(['edu_teacher_tui'=>'edu_teacher_tui','edu_teacher_info'=>'edu_teacher_info'])
            ->where('edu_teacher_tui.teacher_id=edu_teacher_info.teacher_id')//查询条件语句
            ->select();
        return json_encode($data);
	}
	
	//名师简介
	public function teacher_info(){
		$id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');//获取id并过滤  
		if(empty($id)){
			return json_encode(["code"=>1001,"msg"=>"无参数"]);
		}else{
			//老师信息
			$data["teacher"]=Db::table('edu_teacher_info')->where("teacher_id",$id) ->select();
			
			//有多少学生
			$data["student"]=Db::table('edu_buy_recond')->where("teacher_id",$id)->select();
			
			//有多少视频
			$data["video"]=Db::table('edu_video')->where("teacher_id",$id) ->select();
            
			return json_encode($data);
		}
	}
	
	//视频详情页
	public function product(){
		$id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');//获取id并过滤  
		if(empty($id)){
			return json_encode(["code"=>1001,"msg"=>"无参数"]);
		}else{
			//视频信息
			$data["product"]=Db::table('edu_product')->where("teacher_id",$id) ->select();
			//包含视频的链接,老师所有的讲课视频
			$data["video"]=Db::table('edu_video')->where("teacher_id",$id) ->select();
			//与视频相关的老师
			$data["teacher"]=Db::table('edu_teacher_info')->where("teacher_id",$id) ->select();
			
			//老师相关的学生
			$data["student"]=Db::table("edu_buy_recond")->where("teacher_id",$id)->select();
			
			
			return json_encode($data);
		}
	}
////////////////////////////////////	
	//查看订单
	public function buy_sclect(){
		$id=$this->request->post("openid",'','strip_tags,htmlspecialchars');//获取id并过滤  
		if(empty($id)){
			return json_encode(["code"=>1001,"msg"=>"无参数"]);
		}else{
			//购买老师信息
			$data=Db::field('edu_order.*,edu_product.*')
			->table(['edu_order'=>'edu_order','edu_product'=>'edu_product'])
			->where('edu_order.teacher_id=edu_product.teacher_id')
			->where("openid",$id) 
			->select();
            
			return json_encode($data);
		}
	}
	
	//订单详情
	public function buy_detail(){
		$id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');//获取id并过滤  
		if(empty($id)){
			return json_encode(["code"=>1001,"msg"=>"无参数"]);
		}else{
			//老师的所有视频
			$data=Db::field('edu_video.*,edu_full_video.*')//截取表的全部
            ->table(['edu_video'=>'edu_video','edu_full_video'=>'edu_full_video'])
            ->where('edu_video.teacher_id=edu_full_video.teacher_id')//查询条件语句
            ->where("edu_video.teacher_id",$id)
            ->select();
            if(empty($data)){
            	return json_encode(["code"=>1002,"msg"=>"参数错误"]);
			}else{
		        return json_encode($data);
			}
		}
	}
///////////////////////////////////////	
	//购买
	public function buy(){
			$openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
			$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
//			$sum=$this->request->post("buy_sum",'','strip_tags,htmlspecialchars');
			if(!empty($openid&&$teacher_id)){
			//未付款
			
			
			
			//付款成功
				$current=time();//当前时间戳
				$time=time()+2592000;//一个月之后的时间戳
				$expire=Db::table("edu_buy_recond")
				->where("teacher_id",$teacher_id)
				->where("openid",$openid)
				->select();
				
				if(empty($expire)){
				//新会员购买	
					$insert=array("openid"=>$openid,"teacher_id"=>$teacher_id,"expire_time"=>$time);
				    $date=Db::table("edu_buy_recond")->insert($insert);
				}else{
					$times=$expire[0]["expire_time"];
				    if($times>$current){
				    	//未过期续费
					    $time=$times+2592000;
				    }else{
				    	//过期续费
					    $time=time()+2592000;
				    }
					$update=array("expire_time"=>$time);

			        $updatenum=Db::table("edu_buy_recond")
			        ->where("openid",$openid)
			        ->where("teacher_id",$teacher_id)
			        ->update($update);
				}				
				//新购买
//				$insert=array("openid"=>$openid,"teacher_id"=>$teacher_id,"expire_time"=>$time);
//				$date=Db::table("edu_buy_recond")->insert($insert);
				$data=Db::table("edu_buy_recond")
				->where("teacher_id",$teacher_id)
				->where("openid",$openid)
				->select();
				if(empty($data)){
					return json_encode(["code"=>1002,"msg"=>"添加失败"]);
				}else{
					//购买老师的总量
//					$sum=$this->request->post("buy_sum",'','strip_tags,htmlspecialchars');
//					$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
					$buy=Db::table("edu_teacher_buy")->where("teacher_id",$teacher_id)->select();
					$sum=$buy[0]["buy_sum"];
					if(empty($buy)){
						//老师第一次卖出
						$sum=1;
						$inserts=array("teacher_id"=>$teacher_id,"buy_sum"=>$sum);
						$date=Db::table("edu_teacher_buy")->insert($inserts);
					}else{
						//后续卖出
						Db::table('edu_teacher_buy')
                        ->where('teacher_id',$teacher_id)
                        ->setInc('buy_sum');
					}
					return json_encode(["code"=>1000,"msg"=>"添加成功"]);
				}
			}else{
				return json_encode(["code"=>1001,"msg"=>"无参数"]);
			}   
	}
	
	//关注
	public function attention(){
		$openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
		$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
		if(!empty($openid&&$teacher_id)){
			$select=Db::table("edu_teacher_student")
				->where("teacher_id",$teacher_id)
				->where("openid",$openid)
				->select();
			if(empty($select)){
				$insert=array("openid"=>$openid,"teacher_id"=>$teacher_id);
		        $date=Db::table("edu_teacher_student")->insert($insert);
		        return json_encode(["code"=>1000,"msg"=>"关注成功"]);
			}else{
				return json_encode(["code"=>1002,"msg"=>"关注失败"]);
			}
			
		}else{
			return json_encode(["code"=>1001,"msg"=>"无参数"]);
		}
	}
	
	
	
//////////////////////////////////////////////////////////////////
	//无限极分类测试
	public function test(){
		$data['parent']=Db::table("edu_parent_class") ->select();//年级类
		//科目
        $data['sub']=Db::table("edu_sub_class") ->select();
        $tmp=[];
        foreach($data['parent'] as &$row){
			$num=substr_count($row["sub_id_list"],',');
			$tmp=Db::table("edu_sub_class")->where('id','in',$row["sub_id_list"]) ->select();
			foreach($tmp as $row1){
				$pre="|"."-".$row1['name'];
				$row["tree"]['']=$pre.$row["name"];
			}
			
//			exit;
//			dump($row1);
//			exit;
//			if($row["pid"]>0){
//				$pre="|".str_repeat("-",$num);
//			}else{
//				$pre="";
//			}
//			$row["tree"]=$pre.$row["name"];
//			dump($row);
			
		}
		dump($row["tree"]);
		
		
		
		
		
		
		
		$data=Db::table("class_test")->select();
		
		foreach($data as &$row){
			$num=substr_count($row["path"],'-');
//			dump($num);
			if($row["pid"]>0){
				$pre="|".str_repeat("-",$num);
			}else{
				$pre="";
			}
			$row["tree"]=$pre.$row["name"];
//			dump($row);
			
		}
		foreach($data as $row1){
			$arr[]=$row1["path"]."-".$row1["id"];
		}
		array_multisort($arr,$data);
		foreach($data as $row2){
			echo "<p>{$row2["tree"]}</p>";
		}
	}
	
	
}


?>
