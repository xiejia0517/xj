<?php
namespace app\api\model;
use \think\Model;
class T_permission extends Model
{

    protected static function init()
    {
        echo ('T_permission Model init!!-----');
    }

    //和permission_group建立从属关联
    // public function t_permission_group()
    // {
    //     return $this -> belongsTo("t_permission_group","f_permission_group_id","f_id");
    // }

}