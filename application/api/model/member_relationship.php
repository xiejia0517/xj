<?php
namespace app\api\model;
use \think\Model;
class Member_relationship extends Model
{
    protected static function init()
    {
        // echo ('Member_relationship Model init!!-----');
    }
    //注册者member和company建立关系,并给予最高权限0
    public function createRelationMemberToCom($member_id,$company_id)
    {
        $data = [
            'member_id' => $member_id,
            'company_id' => $company_id,
            'role' => '1|2|3|4',
            'iscreat' => 1,
        ];
        $creat_res = Db('member_relationship') -> insert($data);
        return $creat_res;
    }
    //通过member_id获取结果集
    public function getRelationResFromMemberID($member_id)
    {
        $res = Db('member_relationship') ->where('member_id',$member_id) ->select();
        return $res;
    }
    //验证对于某一company所拥有的权限
    public function checkRelationForCompany($company_id)
    {
        $res = Db("member_relationship") ->where('company_id',$company_id) ->find();
        return $res['role'];
    }
    //查询全部关注了某一company的全部用户
    public function getAllMemberFollowaCom($company_id)
    {
        $map['mr.company_id'] = $company_id;
        $map['mr.iscreat'] = array('neq',1);
        //按照模组ID分组并返回每个模组smt_count的最大最小值
        $search_res = Db('member_relationship')->alias('mr')->join('member mb','mr.member_id = mb.member_id','LEFT') ->field(array(
            'mr.member_id ',
            'mr.role',
            'mb.member_name',
            'mb.member_truename',
            'mb.member_avatar',
            'mb.member_sex'
        )) ->where($map) ->select();
        return $search_res;
    }
    //查询全部角色配置信息
    public function getAllRoleInfo()
    {
        $map['role_id'] = array('gt',1);
        $res = Db('member_role') ->where($map) -> select();
        return $res;
    }
    //更新用户角色权限信息
    public function updateMemberRole($company_id,$member_id,$role_str)
    {
        $data = [
            'role' => $role_str
        ];
        $map['company_id'] = $company_id;
        $map['member_id'] = $member_id;
        $res = Db('member_relationship') ->where($map) -> update($data);
        return $res;
    }
    //删除关注用户
    public function delFollMember($company_id,$member_id)
    {
        $map['member_id'] = $member_id;
        $map['company_id'] = $company_id;
        $map['iscreat'] = array('eq',0);
        // $res = $this -> destroy($group_id,true);
        $res = Db('member_relationship') -> where($map) -> delete();
        return $res;
    }
}