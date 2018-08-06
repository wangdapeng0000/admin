<?php
namespace app\wxpay\controller;

use think\Controller;
use think\Request;
require_once '../extend/wxpay/Minipay.php';
use Minipay;


/**
 * 小程序支付
 */
class Minipay extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request; 
        $this->appid  = 'wx446c2e7236458b65';
        $this->secret = '50948be79090c9d318314a64b630a8cc'; 
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

      public function notify()
    {
        $xml = file_get_contents('php://input');
        $myfile = fopen("wxpaynotify.txt", "a");

        fwrite($myfile, "\r\n");
        fwrite($myfile, $xml);
        $minipay = new Minipay();
//        将服务器返回的XML数据转化为数组
        $data = $minipay->xml2array($xml);       
        // 保存微信服务器返回的签名sign
        $data_sign = $data['sign'];
        // sign不参与签名算法
        unset($data['sign']);
        
        $sign = $minipay->makeSign($data, 'QWERTYUIOPASDFGHJKLZXCVBNM123456');
        // dump($sign);
       
        // 判断签名是否正确  判断支付状态
        if ( (trim(strtolower($sign))==trim(strtolower($data_sign))) && ($data['return_code']=='SUCCESS') && ($data['result_code']=='SUCCESS') ) {
      
            // file_put_contents(date("His") . "--------------", $sign);

            $result = true;
        } else {
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
        $openid = $this->request->param('openid','','strip_tags','htmlspecialchars');
       
         $minipay = new Minipay();

        $this->partnerKey = 'QWERTYUIOPASDFGHJKLZXCVBNM123456';
        $res              = array(
            'openid'       => $openid,
            'body'         => '教育edu测试',
            'order_id'     => date("YmdHis", time() + 100) . rand(1, 100),
//            'price'        => $post['final'] * 100,
            'price'        => '1',
            'mch_id'       => '1501095101',
            //订单编号,回调要用到
            'out_trade_no' => $minipay->getNonceStr(),
//            'out_trade_no' => 'my' . date("YmdHis", time() + 600) . rand(1, 100),
            'notify_url'   => 'https://api.csyaxinjs.com/admin/Wxpay/notify',
            'appid'        => 'wx446c2e7236458b65',
            'key'          => $minipay->getNonceStr()
        );

        $unifiedorder         = array(
            'appid'            => $res['appid'],
            'mch_id'           => $res['mch_id'],
            'nonce_str'        => $minipay->getNonceStr(),
            'body'             => $res['body'],
            'out_trade_no'     => $res['out_trade_no'],
            'total_fee'        => $res['price'],
            'spbill_create_ip' => '47.98.37.167',
            'notify_url'       => $res['notify_url'],
            'trade_type'       => 'JSAPI',
            'openid'           => $res['openid'],
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
            'timeStamp' => time(),
            'nonceStr'  => $minipay->getNonceStr(),
            'package'   => 'prepay_id=' . strval($content['prepay_id']),
            'signType'  => 'MD5',
        );
        //加密签名
        $resData['paySign'] = $minipay->makeSign($resData, $this->partnerKey);
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

   
}