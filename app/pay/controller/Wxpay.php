<?php
namespace app\pay\controller;

use think\Controller;
use think\Db;
require_once '../extend/Pay/init.php';


/**
 * 
 */
class Wxpay extends Controller
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function minipay()
	{
		 $data = $this->request->param('','','strip_tags','htmlspecialchars');
        if(empty($data['order_id'])){return json_encode(["code"=>1001,"msg"=>"参数错误!"]);}

		$config = [
		    // 微信支付参数
		    'wechat' => [
		        // 沙箱模式
		        'debug'      => false,
		        // 应用ID
		        'app_id'     => 'wx446c2e7236458b65',
		        // 微信支付商户号
		        'mch_id'     => '1501095101',
		        /*
		         // 子商户公众账号ID
		         'sub_appid'  => '子商户公众账号ID，需要的时候填写',
		         // 子商户号
		         'sub_mch_id' => '子商户号，需要的时候填写',
		        */
		        // 微信支付密钥
		        'mch_key'    => 'QWERTYUIOPASDFGHJKLZXCVBNM123456',
		        // 微信证书 cert 文件
		        'ssl_cer'    => __DIR__ . '',
		        // 微信证书 key 文件
		        'ssl_key'    => __DIR__ . '',
		        // 缓存目录配置
		        'cache_path' => '',
		        // 支付成功通知地址
		        'notify_url' => 'https://api.csyaxinjs.com/edu/Wxpay/notify',
		        // 网页支付回跳地址
		        'return_url' => '',
		    ]
		];
        // 查订单
        $order_info = Db::table('edu_order')->where(['order_id'=>$data['order_id'],'pay_status'=>0])->find();
        $product_title = Db::table('edu_product')->where('teacher_id',$order_info['teacher_id'])->value('name');

        // 更新订单号
        $order_id_new = $this->getNonceStr();
        $order_up = Db::table('edu_order')->where(['order_id'=>$data['order_id'],'pay_status'=>0])->update(['order_id'=>$order_id_new]);
        if (!$order_up) {return json_encode(["code"=>1002,"msg"=>"支付失败请重试!"]);}
        // 支付参数
		$options = [
		    'out_trade_no'     => $order_id_new, // 订单号
		    'total_fee'        => $order_info['order_price'], // 订单金额，**单位：分**
		    'body'             => $product_title, // 订单描述
		    'spbill_create_ip' => '47.98.37.167', // 支付人的 IP
		    'openid'           => $order_info['openid'], // 支付人的 openID
		    'notify_url'       => 'https://api.csyaxinjs.com/edu/Wxpay/notify', // 定义通知URL
		];

	  $pay = new \Pay\Pay($config);
	  try {
		    $result = $pay->driver('wechat')->gateway('miniapp')->apply($options);
		    echo '<pre>';
		    var_export($result);
		} catch (Exception $e) {
		    echo $e->getMessage();
		}
	}
    //随机数
	public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}