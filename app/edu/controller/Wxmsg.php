<?php
namespace app\edu\controller;

use think\Controller;
use think\Db;

/**
 * 
 */
class Wxmsg extends Controller
{
	
	protected $appId;
	protected $appSecret;

	function __construct()
	{

		$this->appId='wx6b05ff1709640b2c';
		$this->appSecret= '514cdf0a8b7d64af5bdcfadfdc97c64b';
		parent::__construct();
	}


    //formid接口 
   public function update_formid()
   {
   	  $data=$this->request->post("",'','strip_tags,htmlspecialchars');
   	  if (!$data['openid']){ return json_encode(["code"=>1001,"err_msg"=>"无参数"]);}
   	     $res = Db::table('edu_formid')->where('openid',$data['openid'])->find(); 
   	     $data['expire_formid']=time()+600000;

   	  if (!$res){
   	     Db::table('edu_formid')->strict(false)->insert($data);
   	  }else{
   	  	if($res['formid_use']==1||$res['expire_formid']<time()){
   	  	 $data['formid_use'] = 0;	
   	  	  Db::table('edu_formid')->where('openid',$data['openid'])->update($data);
   	  	}
   	  }
   }
    // 发送模板消息
   public function send_wxmsg($openid='',$order_id='')
   {
      if (empty($openid)&&empty($order_id)) {exit();}
   	  $url='https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->access_token();
   	  $formid = Db::table('edu_formid')->where('openid',$openid)->value('formid');
      $order = Db::table('edu_order')->where(['openid'=>$openid,'order_id'=>$order_id])->find();
      $product_name = Db::table('edu_product')->where('teacher_id',$order['teacher_id'])->value('name');
   	  $data = json_encode( 
   	  	array(
			  "touser"=>$openid,
			  "template_id"=>"GanqeEb32GAssRrCjAjH5lV-VscqyzmoGpkLr0MiDsE",
			  "page"=>"pages/index/index",
			  "form_id"=>$formid,
			  "data"=>array(
			      "keyword1"=>array(
			          "value"=>date('Y-m-d',$order['pay_time'])
			      ),
			      "keyword2"=>array(
			          "value"=>$product_name
			      ),
			      "keyword3"=>array(
			          "value"=>$order['order_price'].'元'
			      ) ,
			      "keyword4"=>array(
			          "value"=>$order['order_id']
			      ) ,
			      "keyword5"=>array(
			          "value"=>(30*$order['order_sum']).'天'
			      )
			  ),
			  "emphasis_keyword"=>"keyword5.DATA"
			)
   	    );
   	
   	  $result = $this->http_curl($url,$data);
   	  $result = json_decode($result, true);
   	  if ($result['errmsg']=='ok') {
   	  	Db::table('edu_formid')->where('openid',$openid)->update(['formid_use'=>1]);
   	  }
   	  return json($result); 
   }
    

   // W4JGQTLmaj-AeFoU9Vy2KvyryA7pxz2TKxgzg1W0gP4
    
     /**** access_token ****/
     
     public function access_token()
      {            
                    
        $res = Db::table('edu_token')->where('appid',$this->appId)->field('access_token,expire_token')->find();  
   
	        if ($res['expire_token'] < time()) 
	          {           
	            $url  = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=".$this->appSecret;  
	            $result = $this->http_curl($url);  
	            $jsoninfo    = json_decode($result, true);           
	            $access_token  = $jsoninfo["access_token"];           
	            if ($access_token) 
	            {             
	              $data['expire_token'] = time() + 7000;             
	              $data['access_token'] = $access_token;  
	             Db::table('edu_token')->where('appid',$this->appId)->update($data);         
	            }         
	           }else {           
	                   $access_token = $res['access_token'];       
	                  }         
	             return $access_token;
      
      }//getToken


    //随机字符串
    public function getRandStr($length = 32)
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
