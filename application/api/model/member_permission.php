<?php
namespace app\api\model;
use \think\Model;
class Member_permission extends Model
{
    protected static function init()
    {
        // echo ('Member_permission Model init!!-----');
    }
    //通过member_id获取权限值
    public function getPermissionLevel($member_id)
    {
        
    }
}