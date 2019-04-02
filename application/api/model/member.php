<?php
namespace app\api\model;
use \think\Model;
use think\Db;
class Member extends Model
{
    protected static function init()
    {
        // echo('Member is init------');
    }
    
    public function getRelationship($memID)
    {
        //获取用户所属的company信息
        $mem_relation_res = Db('member_relationship') -> where('member_id',$memID) ->select();
        return $mem_relation_res;
    }
    public function getRelationIsRegiest($memID,$iscreat)
    {
        $map['member_id'] = $memID;
        $map['iscreat'] = $iscreat;
        $mem_relation_res = Db('member_relationship') -> where($map) ->select();
        return $mem_relation_res;
    }
    public function getSingleInfomation($memID)
    {
        $mem_single_info = Db('member')->find($memID);
        return $mem_single_info;
    }
    //修改昵称
    public function editMemberTtuename($member_id)
    {
        
    }
    /**
     * 获取member_id
     */
    public function getMemberId($unionid)
    {
        $member_id = Db('member') ->where('member_wxunionid',$unionid) ->find();
        return $member_id['member_id'];
    }
}