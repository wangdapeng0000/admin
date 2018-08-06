<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17
 * Time: 18:50
 */
namespace app\admin\model;
use think\Model;


class Admin extends Model
{
    protected $pk = "admin_id";
    protected $insert = ["password", "status"];
    protected $readonly = ["admin_id", "status"];
    protected $autoWriteTimestamp = "datetime";

    public function setStatusAttr()
    {
        return "consumer";
    }
    public function setPasswordAttr($passwoard)
    {
        return $passwoard;
    }

    public function getStatusAttr($status)
    {
        $arr = ["consumer" =>"普通用户","manager" =>"管理员"];
        return $arr[$status];
    }
}


    ?>