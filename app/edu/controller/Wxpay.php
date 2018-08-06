<?php
namespace app\edu\controller;

use think\Controller;
use think\Request;
require_once '../extend/wxpay/Minipay.php';
use Minipay;
use \think\Db;

/**
 * 小程序支付
 */
class Wxpay extends Controller
{
	public function __construct(Request $request)
	{
		$this->request = $request; 
		$this->appid  = 'wx6b05ff1709640b2c';
    $this->secret = '514cdf0a8b7d64af5bdcfadfdc97c64b'; 
		parent::__construct();  
	}
    /*
      *app.js调用getOpenid接口获取openid
      *本接口属于功能组件
      */
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
   // 接受支付通知更改订单状态
      public function notify()
    {
        $xml = file_get_contents('php://input');

        // $xml = '<xml><appid><![CDATA[wx6b05ff1709640b2c]]></appid>
        //         <bank_type><![CDATA[CFT]]></bank_type>
        //         <cash_fee><![CDATA[1]]></cash_fee>
        //         <fee_type><![CDATA[CNY]]></fee_type>
        //         <is_subscribe><![CDATA[N]]></is_subscribe>
        //         <mch_id><![CDATA[1500462722]]></mch_id>
        //         <nonce_str><![CDATA[aw2pi0x638f4mgnbk8jhh0pone4czgf0]]></nonce_str>
        //         <openid><![CDATA[o7KX947Csqdkpcu78IJAZtpq5N5A]]></openid>
        //         <out_trade_no><![CDATA[aa10pk08hkbw28d7pfhnit3imo6dwpxg]]></out_trade_no>
        //         <result_code><![CDATA[SUCCESS]]></result_code>
        //         <return_code><![CDATA[SUCCESS]]></return_code>
        //         <sign><![CDATA[9A190A281A2CB661C34BDF1FB9096E43]]></sign>
        //         <time_end><![CDATA[20180727141549]]></time_end>
        //         <total_fee>1</total_fee>
        //         <trade_type><![CDATA[JSAPI]]></trade_type>
        //         <transaction_id><![CDATA[4200000121201807277154246247]]></transaction_id>
        //         </xml>'; 
        // $myfile = fopen("wxpaynotify.txt", "a");

        // fwrite($myfile, "\r\n");
        // fwrite($myfile, $xml);
        $minipay = new Minipay();
//        将服务器返回的XML数据转化为数组
        $data = $minipay->xml2array($xml);  
       
        // 保存微信服务器返回的签名sign
        $data_sign = $data['sign'];
        // sign不参与签名算法
        unset($data['sign']);
        $sign = $minipay->makeSign($data, 'QWERTYUIOPASDFGHJKLZXCVBNM123456');
         
        // 判断签名是否正确  判断支付状态
        if ( (trim(strtolower($sign))==trim(strtolower($data_sign))) && ($data['return_code']=='SUCCESS') && ($data['result_code']=='SUCCESS') ) {
           
           //判断订单状态
          $order_info =  Db::table('edu_order')->where('order_id',$data['out_trade_no'])->find();

           //订单状态为未支付
           if ($order_info['pay_status']==0) {
               $res_recond =  Db::table('edu_buy_recond')->where(['openid'=>$data['openid'],'teacher_id'=>$order_info['teacher_id']])->find();
              
                //一个月的时间戳
                $sub_time = 30*24*3600; 
                // 判断购买记录是否存在
                if ($res_recond) {
                   $res_recond_1=Db::table('edu_buy_recond')->where(['openid'=>$data['openid'],'teacher_id'=>$order_info['teacher_id']])->setInc('expire_time',$sub_time);

                }else{
                   $res_recond_2=Db::table('edu_buy_recond')->insert(['teacher_id'=>$order_info['teacher_id'],'openid'=>$data['openid'],'expire_time'=>time()+$sub_time]);
                }
                
                 // 更新老师收入表
                $res_teacher_buy =  Db::table('edu_teacher_buy')->where(['teacher_id'=>$order_info['teacher_id']])->find();

                if ($res_teacher_buy) {
                     $res_teacher_buy_1=Db::table('edu_teacher_buy')->where(['teacher_id'=>$order_info['teacher_id']])->setInc('buy_sum',$order_info['order_sum']);
                     $res_teacher_buy_2=Db::table('edu_teacher_buy')->where(['teacher_id'=>$order_info['teacher_id']])->setInc('buy_price',$order_info['order_price']);
                }else{
                     $res_teacher_buy_3=Db::table('edu_teacher_buy')->insert(['buy_price'=>$order_info['order_price'],'buy_sum'=>$order_info['order_sum'],'teacher_id'=>$order_info['teacher_id']]);
                }

                 // 更新师生关系表
                $res_teacher_student =  Db::table('edu_teacher_student')->where(['openid'=>$data['openid'],'teacher_id'=>$order_info['teacher_id']])->find();
                if(!$res_teacher_student){
                    Db::table('edu_teacher_student')->insert(['openid'=>$data['openid'],'teacher_id'=>$order_info['teacher_id']]);
                }
                //更新订单表
                 $res_order=Db::table('edu_order')->where(['order_id'=>$data['out_trade_no'],'pay_status'=>0])->update(['pay_status'=>1,'pay_time'=>time()]);
                
                if ($res_order) {
                    $this->order_statistics($order_info['teacher_id']);
                    // 发送模板消息
                    $url="https://".$_SERVER['HTTP_HOST']."/edu/Wxmsg/send_wxmsg?openid=".$data['openid']."&order_id=".$data['out_trade_no'];
                    $this->http_curl($url);
                    $result = true;
                }

           }else{
             $result = true;
           }
                       
        }else{
           $result = false;
        }
        // 返回状态给微信服务器
       
        if ($result) {
            exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
        } else {
            exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>');
        }
    }

   //小程序支付
	public function minipay()
    {
       $data['order_id'] = $this->request->post('order_id','','strip_tags','htmlspecialchars');
        if(!$data['order_id']){return json_encode(["code"=>1001,"msg"=>"参数错误!"]);}      
        // 查订单
        $order_info = Db::table('edu_order')->where(['order_id'=>$data['order_id'],'pay_status'=>0])->find();
        $product_title = Db::table('edu_product')->where('teacher_id',$order_info['teacher_id'])->value('name');
        $minipay = new Minipay();
        $this->partnerKey = 'QWERTYUIOPASDFGHJKLZXCVBNM123456';
        // 更新订单号
        $order_id_new = $minipay->getNonceStr();
        $order_up = Db::table('edu_order')->where(['order_id'=>$data['order_id'],'pay_status'=>0])->update(['order_id'=>$order_id_new]);
        if (!$order_up) {return json_encode(["code"=>1002,"msg"=>"支付失败请重试!"]);}
        // 统一下单参数
        $unifiedorder         = array(
            'appid'            => 'wx6b05ff1709640b2c',
            'mch_id'           => '1500462722',
            'nonce_str'        => $minipay->getNonceStr(),
            'body'             => $product_title,
            'out_trade_no'     => $order_id_new,
            'total_fee'        => $order_info['order_price']*100,
            'spbill_create_ip' => '47.98.37.167',
            'notify_url'       => 'https://api.csyaxinjs.com/edu/Wxpay/notify',
            'trade_type'       => 'JSAPI',
            'openid'           => $order_info['openid'],
        );
       
        $unifiedorder['sign'] = $minipay->makeSign($unifiedorder, $this->partnerKey);
       
        //请求数据
        $xmldata = $minipay->array2xml($unifiedorder);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $res = $minipay->curl_post_ssl($url, $xmldata);
        if (!$res) {
            $minipay->return_err("Can't connect the server");
        }
        $content = $minipay->xml2array($res);
        if (strval($content['return_code']) == 'FAIL') {
            $minipay->return_err(strval($content['return_msg']));
        }
        if (strval($content['result_code']) == 'FAIL') {
            $minipay->return_err(strval($content['return_msg']));
        }

        //拼接小程序的接口数据
        $resData = array(
            'appId'     => $content['appid'],
            'timeStamp' => (string)time(),
            'nonceStr'  => $minipay->getNonceStr(),
            'package'   => 'prepay_id=' . strval($content['prepay_id']),
            'signType'  => 'MD5',
        );
        //加密签名
        $resData['paySign'] = $minipay->makeSign($resData, $this->partnerKey);
        $resData['order_id'] = $order_id_new;
        return json_encode($resData);
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



  //按月统计订单
  public function order_statistics($teacher_id){
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
   
}