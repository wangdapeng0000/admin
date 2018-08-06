<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
require_once '../extend/aliyun-php-sdk/aliyun-php-sdk-core/Config.php';
use vod\Request\V20170321 as vod;
use DefaultProfile;
use DefaultAcsClient;  

/**
 * 
 */
class Oss extends Controller
{
	protected $accessKeyId;
    protected $accessKeySecret;
  
	function __construct()
	{
		$this->accessKeyId = 'LTAIPgYvM5nNwd2s';
		$this->accessKeySecret = 'sV1cZAds5T9uFl5l6L6X85swQeJRdb';
		parent::__construct();
	} 



	// 阿里云点播视频上传
    //初始化
    function init_vod_client($accessKeyId, $accessKeySecret) {
        $regionId = 'cn-shanghai';  // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域
        $profile = DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret);
        return new DefaultAcsClient($profile);
    }
    
    //获取视频上传地址和凭证
    function aliyun_upload_video($data) {
        $client = $this->init_vod_client($this->accessKeyId, $this->accessKeySecret);
        $request = new vod\CreateUploadVideoRequest();
        $request->setTitle($data['video_name']);        // 视频标题(必填参数)
        $request->setFileName($data['video_name'].'.mp4'); // 视频源文件名称，必须包含扩展名(必填参数)
        $request->setDescription($data['video_name']);  // 视频源文件描述(可选)
        $request->setCoverURL("https://".$_SERVER['HTTP_HOST'].$data['video_img_url']); // 自定义视频封面(可选)
        // $request->setTags("测试1112,测试1422"); // 视频标签，多个用逗号分隔(可选)
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }


    
    function get_video_list() {
        $client = $this->init_vod_client($this->accessKeyId, $this->accessKeySecret);
        $request = new vod\GetVideoListRequest();
        // 示例：分别取一个月前、当前时间的UTC时间作为筛选视频列表的起止时间
        $localTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $utcNow = gmdate('Y-m-d\TH:i:s\Z');
        $utcMonthAgo = gmdate('Y-m-d\TH:i:s\Z', time() - 2*24*30*86400);
        date_default_timezone_set($localTimeZone);
        $request->setStartTime($utcMonthAgo);   // 视频创建的起始时间，为UTC格式
        $request->setEndTime($utcNow);          // 视频创建的结束时间，为UTC格式
        #$request->setStatus('Uploading,Normal,Transcoding');  // 视频状态，默认获取所有状态的视频，多个用逗号分隔
        #$request->setCateId(0);               // 按分类进行筛选
        // $request->setPageNo(1);
        $request->setPageSize(200);
        $request->setAcceptFormat('JSON');
        
        $data =(array)($client->getAcsResponse($request)->VideoList->Video);
        // dump($data);
        $v_list = array();
        for ($i=0; $i <count($data) ; $i++) { 
            $v_list[] = $data[$i]->VideoId;
        }        
        dump($v_list);
    }
    // 删除视频
    function delete_videos($videoIds) {
         if ($videoIds==0) { return ;}
        $client = $this->init_vod_client($this->accessKeyId, $this->accessKeySecret);
            $request = new vod\DeleteVideoRequest();
            $request->setVideoIds($videoIds);   // 支持批量删除视频；videoIds为传入的视频ID列表，多个用逗号分隔
            $request->setAcceptFormat('JSON');
            $client->getAcsResponse($request);
    }

    // 查询分类
    function get_categories($cateId=-1, $pageNo=1, $pageSize=10) {
         $client = $this->init_vod_client($this->accessKeyId, $this->accessKeySecret);
         $request = new vod\GetCategoriesRequest();
         $request->setCateId($cateId);   // 分类ID，默认为根节点分类ID即-1
         $request->setPageNo($pageNo);
         $request->setPageSize($pageSize);
         $request->setAcceptFormat('JSON');
         dump($client->getAcsResponse($request));
    }

}