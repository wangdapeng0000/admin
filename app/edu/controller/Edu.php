<?php
namespace app\edu\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\facade\Session;
use think\db\Query;

use app\admin\controller\Oss;


class Edu extends Controller
{
	
	protected $request;
    protected $appid;
    protected $secret;
	
	public function __construct(Request $request)
    {
		$this->request = $request;
		$this->appid = 'wx6b05ff1709640b2c';
		$this->secret = '514cdf0a8b7d64af5bdcfadfdc97c64b';
		parent::__construct();
    }
    
    
    public function time(){
    	$time = $this->request->param('time','','strip_tags','htmlspecialchars');
    	$data=date("Y-m-d H-s-m",$time);
    	return json_encode($data);
    }
    
    
    public function GetOpenid()
     {
         //通过用户的code换取openid
         $code = $this->request->param('code','','strip_tags','htmlspecialchars');
         if ($code) {
              $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appid."&secret=".$this->secret."&js_code=".$code."&grant_type=authorization_code";
              $res = $this->http_curl($url);
              return $res;
            }else{
              exit('code不能为空！');
            }   
     }
     
     //用户验证
     public function update_userInfo(){
        $userinfo=$this->request->post("",'','strip_tags,htmlspecialchars');
        $data=$userinfo['userInfo'];
        $data['last_time']=time();
        $select=Db::table("edu_user")->where("openid",$data['openid'])->find();
        if(empty($select)){
           $insert=Db::table("edu_user")->strict(false)->insert($data);
        }else{
           $data['login_num']=$select["login_num"]+1;
           $update=Db::table("edu_user")->where("openid",$data['openid'])->strict(false)->update($data);
        }
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
			return json_encode(["code"=>1002,"err_msg"=>"您无权观看"]);
		}
		$time=$data[0]["expire_time"];
		if($current<$time){//未过期
			return json_encode(["code"=>1000,"err_msg"=>"您的订单还有时间"]);
		}else{//已过期
			return json_encode(["code"=>1001,"err_msg"=>"您的订单已过期"]);
		}
		
    }
	
	//分类页
	public function parent_class(){
		$data['parent']=Db::table("edu_parent_class") ->select();//年级类
		//科目
        $data['sub']=array();
        for($i=0;$i<count($data['parent']);$i++){
        	$data['sub'][$data['parent'][$i]['id']]=Db::table("edu_sub_class")->where('id',"in",$data['parent'][$i]["sub_id_list"])->select();
        }
        //老师
        $data['teacher_parent']=array();
        $temp = array();
        for($i=0;$i<count($data['parent']);$i++){
        	$temp = Db::table("edu_teacher_info")->where('parent_id',$data['parent'][$i]['id'])->select();

            if(!empty($temp)){
                for ($j=0; $j <count($temp) ; $j++) { 
                  $temp[$j]['head_img'] = "https://".$_SERVER['HTTP_HOST'].$temp[$j]['head_img'];
            	  $data['teacher_parent'][$i+1][$j]=$temp[$j];
                }
            }

        }
         
		return json_encode($data);
	}
	
    //首页
	public function index(){
		$temp = array();
		$temp=Db::table("edu_img")->where("class","index")->select();//公共图
		for($i=0;$i<count($temp);$i++){
			$temp[$i]["img_url"]="https://".$_SERVER['HTTP_HOST'].$temp[$i]['img_url'];
		}
		$data["img"]=$temp;
		
		$menu=array(); 
		$menu=Db::table("edu_menu")->order('id asc') ->select();//菜单
		for($i=0;$i<count($menu);$i++){
			if(!empty($menu)){
			$menu[$i]["menu_img"]="https://".$_SERVER['HTTP_HOST'].$menu[$i]['menu_img'];
			}
		}
		$data["menu"]=$menu;
		
		//推荐视频
		$coure=array();
		$coure=Db::field('edu_product_tui.*,edu_product.*')//截取表的全部
            ->table(['edu_product_tui'=>'edu_product_tui','edu_product'=>'edu_product'])
            ->where('edu_product_tui.product_id=edu_product.id')//查询条件语句
            ->select();	
        for($i=0;$i<count($coure);$i++){
			if(!empty($coure)){
			$coure[$i]["img_url"]="https://".$_SERVER['HTTP_HOST'].$coure[$i]['img_url'];
			}
		}
		$data["course"]=$coure;

        //推荐老师
        $teacher=array();
		$teacher=Db::field('edu_teacher_tui.*,edu_teacher_info.*')//截取表的全部
            ->table(['edu_teacher_tui'=>'edu_teacher_tui','edu_teacher_info'=>'edu_teacher_info'])
            ->where('edu_teacher_tui.teacher_id=edu_teacher_info.teacher_id')//查询条件语句
            ->select();
            for($i=0;$i<count($teacher);$i++){
            	if(!empty($teacher)){
			        $teacher[$i]["head_img"]="https://".$_SERVER['HTTP_HOST'].$teacher[$i]['head_img'];
			    }
            }
            $data["teacher"]=$teacher;
		return json_encode($data);
	}
	//名师推荐
	public function teacher_tui(){
		$teacher=array();
		$teacher=Db::field('edu_teacher_tui.*,edu_teacher_info.*')//截取表的全部
            ->table(['edu_teacher_tui'=>'edu_teacher_tui','edu_teacher_info'=>'edu_teacher_info'])
            ->where('edu_teacher_tui.teacher_id=edu_teacher_info.teacher_id')//查询条件语句
            ->select();
            for($i=0;$i<count($teacher);$i++){
            	if(!empty($teacher)){
			        $teacher[$i]["head_img"]="https://".$_SERVER['HTTP_HOST'].$teacher[$i]['head_img'];
			    }
            }
            $data=$teacher;
		return json_encode($data);
	}
	
	//名师简介
	public function teacher_info(){
		$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');//获取id并过滤  
	    $openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
		if(empty($teacher_id)){
			return json_encode(["code"=>1001,"err_msg"=>"无参数"]);
		}else{
			//老师信息
			$teacher=array();
			$teacher=Db::table('edu_teacher_info')->where("teacher_id",$teacher_id) ->find();

            $teacher['head_img'] = "https://".$_SERVER['HTTP_HOST'].$teacher['head_img'];
            $data['teacher']=$teacher;
            
            $product=array();
			$product=Db::table('edu_product')->where("teacher_id",$teacher_id) ->find();
			$product["img_url"]="https://".$_SERVER['HTTP_HOST'].$product["img_url"];
			$data["product"]=$product;
			//有多少学生
			$student_openid = Db::table('edu_buy_recond')->where("teacher_id",$teacher_id)->field('openid')->select();
            $student = array();
            for ($i=0; $i <count($student_openid) ; $i++) { 
            	 $student[]=Db::table('edu_user')->where($student_openid[$i])->field('nickName,avatarUrl')->find();
            }
            //关注状态
            $res=Db::table("edu_teacher_student")->where("openid",$openid)->where("teacher_id",$teacher_id)->value("status");
            if($res){
            	$data['teacher']["status"]=$res;
            }else{
            	$data['teacher']["status"]=0;
            }
            $data["student"]=$student;
			//有多少视频
			$video=array();
			$video=Db::table('edu_video video, edu_full_video full')
                   ->where('video.video_id = full.video_id')
                   ->field('video.*,full.aliyun_video_id,full.full_video_url,full.up_time')
                   ->where("video.teacher_id",$teacher_id)
                   ->select();
			for($i=0;$i<count($video);$i++){
			    $video[$i]['teacher_name']=Db::table('edu_teacher_info')->where("teacher_id",$video[$i]['teacher_id']) ->value('name');
		    }
			for($i=0;$i<count($video);$i++){
            	if(!empty($video)){
			        $video[$i]["video_img_url"]="https://".$_SERVER['HTTP_HOST'].$video[$i]['video_img_url'];
			    }
            }
            $data["video"]=$video;
            
			return json_encode($data);
		}
	}
	//我的关注
	public function guanzhu()
	{
	    $openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
	    $teacher_id=Db::table("edu_teacher_student")->where("openid",$openid)->where("status",1)->column("teacher_id");
	    $data=Db::table('edu_teacher_info')->where("teacher_id",'in',$teacher_id) ->select();
	    if(!empty($data)){
			for($i=0;$i<count($data);$i++){
			    $data[$i]["head_img"]="https://".$_SERVER['HTTP_HOST'].$data[$i]['head_img'];
			}
        }
       return json_encode($data);
	}
/////////////////////////	
//	我的视频
	public function my_buy(){
		$openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
		if (!$openid) {return json_encode(["code"=>1001,"err_msg"=>"无参数"]);}
		$teacher_id=Db::table("edu_buy_recond")
		->where("openid",$openid)
		->column("teacher_id");
		
		$data["teacher"]=Db::table('edu_teacher_info')->where("teacher_id",'in',$teacher_id) ->select();
		if(!empty($data["teacher"])){
			for($i=0;$i<count($data["teacher"]);$i++){
			    $data["teacher"][$i]["head_img"]="https://".$_SERVER['HTTP_HOST'].$data["teacher"][$i]['head_img'];
			}
       }
       return json_encode($data);
	}
	
	//视频浏览量
	public function browse_num(){
		$video_id=$this->request->post("video_id",'','strip_tags,htmlspecialchars');
		Db::table('edu_video')
        ->where('video_id',$video_id)
        ->setInc('browse_num');
        
	}
	
	//观看视频
	public function play_video(){
		$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
		$openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
		$expire_time=Db::table("edu_buy_recond")
		            ->where("openid",$openid)
		            ->where("teacher_id",$teacher_id)
		            ->value("expire_time");
		$time=time();
		if($expire_time<$time){
			return json_encode(["code"=>1000,"err_msg"=>"购买已过期"]);
		}
		
		if (!$teacher_id) {return json_encode(["code"=>1001,"err_msg"=>"无参数"]);}
		$data['video']=Db::table('edu_video video, edu_full_video full')
                   ->where('video.video_id = full.video_id')
                   ->field('video.*,full.aliyun_video_id,full.full_video_url')
                   ->where("video.teacher_id",'in',$teacher_id)
                   ->select();
        if(!empty($data['video'])){
			for($j=0;$j<count($data["video"]);$j++){
				$data["video"][$j]["time"]=intval(($expire_time - $time)/86400);
			    $data["video"][$j]["video_img_url"]="https://".$_SERVER['HTTP_HOST'].$data["video"][$j]['video_img_url'];
			}
        }
        return json_encode($data);
	}
///////////////////////////	
	
	//视频详情页
	public function product(){
		$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');//获取id并过滤  
		if(empty($teacher_id)){
			return json_encode(["code"=>1001,"err_msg"=>"无参数"]);
		}else{
			//视频信息
			$product=array();
			$product=Db::table('edu_product')->where("teacher_id",$teacher_id) ->find();
			$product["img_url"]="https://".$_SERVER['HTTP_HOST'].$product["img_url"];
			$data["product"]=$product;

			//包含视频的链接,老师所有的讲课视频
			$video=array();
			$video=Db::table('edu_video video, edu_full_video full')
                   ->where('video.video_id = full.video_id')
                   ->field('video.*,full.aliyun_video_id,full.full_video_url,full.up_time')
                   ->where("video.teacher_id",$teacher_id)
                   ->select();
			for($i=0;$i<count($video);$i++){
            	if(!empty($video)){
			        $video[$i]["video_img_url"]="https://".$_SERVER['HTTP_HOST'].$video[$i]['video_img_url'];
			    }
            }
            $data["video"]=$video;
            
			//与视频相关的老师
			$teacher=array();
			$teacher=Db::table('edu_teacher_info')->where("teacher_id",$teacher_id) ->find();
            $teacher['head_img'] = "https://".$_SERVER['HTTP_HOST'].$teacher['head_img'];
            $data['teacher']=$teacher;
			
			//老师相关的学生
			$student=array();
			$student=Db::field('edu_buy_recond.*,edu_user.nickName,avatarUrl')//截取表的全部
            ->table(['edu_buy_recond'=>'edu_buy_recond','edu_user'=>'edu_user'])
            ->where('edu_buy_recond.openid=edu_user.openid')//查询条件语句
            ->where("teacher_id",$teacher_id)
            ->select();
            $data["student"]=$student;
			
			return json_encode($data);
		}
	}
////////////////////////////////////	
	//查看订单
	public function my_order(){
		$id=$this->request->post("openid",'','strip_tags,htmlspecialchars');//获取id并过滤  
		if(empty($id)){
			return json_encode(["code"=>1001,"err_msg"=>"无参数"]);
		}else{
			$data=Db::table("edu_order")->where("openid",$id)->field("order_id,teacher_id,order_price,pay_status")->select();
			for($j=0;$j<count($data);$j++){
				if(!empty($data)){
					$data[$j]["product"]=Db::table("edu_product")->where("teacher_id",$data[$j]["teacher_id"])->field("name,img_url,course_num")->find();
					$data[$j]["product"]["img_url"]="https://".$_SERVER['HTTP_HOST'].$data[$j]["product"]["img_url"];
					$data[$j]["teacher_name"]=Db::table('edu_teacher_info')->where("teacher_id",$data[$j]["teacher_id"])->value("name");
				}
			}
			return json_encode($data);
		} 
	}
	
	//订单详情
	public function my_order_info(){
		$data=$this->request->post("",'','strip_tags,htmlspecialchars');
		if(!$data["order_id"]){return json_encode(["code"=>1001,"err_msg"=>"无参数"]);}
			$data["order"]=Db::table("edu_order")->where("order_id",$data["order_id"])->find();
			$data["teacher"]=Db::table("edu_teacher_info")->where("teacher_id",$data['order']['teacher_id'])->field("name,head_img,grader")->find();
			$data["teacher"]["head_img"]="https://".$_SERVER['HTTP_HOST'].$data["teacher"]["head_img"];
			$data["product"]=Db::table("edu_product")->where("teacher_id",$data['order']['teacher_id'])->field("name,img_url,end_time")->find();
			$data["product"]["img_url"]="https://".$_SERVER['HTTP_HOST'].$data["product"]["img_url"];
            if($data["order"]["pay_status"]==0){
            	$data["order"]['pay_time']='未支付';
            	$data["order"]["expire_time"]='未支付';
            }else{
			$data["order"]['pay_time']=date("Y-m-d H-s-m",$data["order"]['pay_time']);
            //结束时间
            $data["order"]["expire_time"]=Db::table("edu_buy_recond")
                                         ->where("openid",$data["order"]["openid"])
                                         ->where("teacher_id",$data["order"]["teacher_id"])
                                         ->value("expire_time");
             $data["order"]["expire_time"]=date("Y-m-d H-s-m",$data["order"]['expire_time']);                    
	        }
		return json_encode($data);
	}
    //删除订单
	public function order_del(){
      $data=$this->request->post("",'','strip_tags,htmlspecialchars');
      if(!empty($data)){
	      $data["pay_status"]=0;
	      $res=Db::table("edu_order")->where($data)->find();
	      if($res){
	      	$del = Db::table("edu_order")->where($data)->delete();
	      	if($del){
	      		$list['code']=200;
	      		$list['err_err_msg']="订单删除成功!";
	      	}else{
	      		$list['code']=400;
	      		$list['err_err_msg']="订单删除失败!";
	      	}
	      	  
	      }else{
	      	  $list['code']=401;
	      	  $list['err_err_msg']="订单不存在!";
	      }
	        return json_encode($list);
	     }else{
           return json_encode(["code"=>1001,"err_msg"=>"无参数"]);
	    }

	}
	//提交订单
	public function order_add(){
			$data=$this->request->post("",'','strip_tags,htmlspecialchars');
			

			if(!empty($data['openid']&&$data['teacher_id'])){
				$res=Db::table("edu_full_video")->where("teacher_id",$data['teacher_id'])->value("full_video_url");
				if(!$res){return json_encode(["code"=>1000,"err_msg"=>"该老师还未上传视频"]);}
				$data["pay_status"]=0;
				$temp =$data;
				$res=Db::table("edu_order")->where($data)->find();
				if(empty($res)){
					$data["order_id"]=$this->getNonceStr();
					$data['order_price'] = 30;
					$data['order_sum'] = 1;
					$data['order_time']=time();
					$insert=Db::table("edu_order")->insert($data);
					if($insert){
						$list['code']=200;
						$list['order_id']=Db::table("edu_order")->where($temp)->value('order_id');
					}else{
						$list['code']=400;
						$list['err_err_msg']="网络错误!请重新提交!";
					}
					
				}else{
					$list['code']=201;
					$list['order_id']=$res['order_id'];
					$list['err_err_msg']="您有未支付的订单!";
				}
				return json_encode($list);
			}else{
				return json_encode(["code"=>1001,"err_msg"=>"无参数"]);
			}   
	}
	////////////////////////////////////////////////////////////////////////////////////////////////
	//关注
	public function attention(){
		$openid=$this->request->post("openid",'','strip_tags,htmlspecialchars');
		$teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
		if(!empty($openid&&$teacher_id)){
			$select=Db::table("edu_teacher_student")
				->where("teacher_id",$teacher_id)
				->where("openid",$openid)
				->find();
			if(empty($select)){
				$insert=array("openid"=>$openid,"teacher_id"=>$teacher_id);
		        $date=Db::table("edu_teacher_student")->insert($insert);
		        return json_encode(["code"=>1000,"err_msg"=>"关注成功"]);
			}else{
				if($select['status']==0){
					$status=1;
				$data=Db::table('edu_teacher_student')
				->where("openid",$openid)
				->where("teacher_id",$teacher_id)
				->update(['status' => $status]);
				return json_encode(["code"=>1002,"err_msg"=>"关注成功"]);
				}else{
				$status=0;
				$data=Db::table('edu_teacher_student')
				->where("openid",$openid)
				->where("teacher_id",$teacher_id)
				->update(['status' => $status]);
				return json_encode(["code"=>1003,"err_msg"=>"取消关注成功"]);
			}
			}
		}else{
			return json_encode(["code"=>1001,"err_msg"=>"无参数"]);
		}
	}
	
	
	
//////////////////////////////////////////////////////////////////////////////////////
	//按月统计订单
	public function order_statistics(){
        $teacher_id=$this->request->post("teacher_id",'','strip_tags,htmlspecialchars');
        //获取当前时间
        $m = date('m',time());
        $y = date("Y",time());
        if(!empty($teacher_id)){
        	$res=Db::table("edu_teacher_income")
        	->where('teacher_id',$teacher_id)
        	->where("year_now",$y)
        	->find();
        	//判断是否有这条数据
        	if(empty($res)){//无则添加
        		$insert=array("year_now"=>$y,"teacher_id"=>$teacher_id,$m."_order_sum"=>1,$m."_price_sum"=>10);
        		$date=Db::table("edu_teacher_income")->insert($insert);
        	}else{
        		$order_sum=$res[$m."_order_sum"]+1;
        		$price_sum=$res[$m."_price_sum"];
        		//分红细则，订单数小于等于50
        		if($order_sum<=50){
        			$price_sum=$price_sum+10;
        			//或大于50小于等于200
        		}elseif($order_sum>50&&$order_sum<=200){
        			$price_sum=$price_sum+15;
        		}else{
        			$price_sum=$price_sum+20;
        		}
        		$update=array($m."_order_sum"=>$order_sum,$m."_price_sum"=>$price_sum); 
        		$data=Db::table("edu_teacher_income")
        		->where("teacher_id",$teacher_id)
        		->where("year_now",$y)
        		->update($update);
        	}
		    return json_encode(["code"=>1000,"err_msg"=>"成功"]);
        }else{
        	return json_encode(["code"=>1001,"err_msg"=>"无参数"]);
        }
	}
//////////////////////////////////////////////////////////////////
	public function test_v()
	{
		$VideoId = '88fc90bf6fa84ea7aec4f30ea784f8fb';
		dump($this->get_video_url($VideoId));
	}
    //获取阿里云视频地址
	public function get_video_url($VideoId)
	{
        $oss = new Oss();
        $url=$oss -> get_video_addr($VideoId);
        return $url;
	}
	
	public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
	

	 //模拟请求get，post  
      public function http_curl($url, $data = null)
      {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
      }//curl
	
}


