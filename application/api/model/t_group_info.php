<?php
namespace app\api\model;
use \think\Model;
class T_group_info extends Model
{
    protected static function init()
    {
        // echo ('T_group_info Model init!!-----');
        /**
         * 钩子函数
         * 删除之前检查数据是否存在
         */
        // self::event('before_delete',function($data){
        //     dump($data);die;
        //     $map['f_company_id'] = $data['f_company_id'];
        //     $map['f_id'] = $data['f_id'];

        //     $res = Db('t_group_info') -> where($map) -> find();
        //     if($res)
        //     {
        //         return true;
        //     }
        //     else
        //     {
        //         return false;
        //     }
        // });
    }
    

     /**
     * 新建产线数据
     */
    public function creatGroupInfo($company_id,$group_name)
    {
        $data = [
            'f_group_name' => $group_name,
            'f_company_id' => $company_id,
        ];
        $res = Db('t_group_info') -> insertGetId($data);
        return $res;
    }
    /**
     * 修改产线数据
     */
    public function editGroupInfo($company_id,$group_name,$group_id)
    {
        $data = [
            'f_group_name' => $group_name,
        ];
        $map['f_id'] = $group_id;
        $map['f_company_id'] = $company_id;
        $res = Db('t_group_info') -> where($map) -> update($data);
        return $res;
    }
    /**
     * 删除产线数据
     */
    public function deleteGroupInfo($company_id,$group_id)
    {
        $map['f_id'] = $group_id;
        $map['f_company_id'] = $company_id;
        // $res = $this -> destroy($group_id,true);
        $res = Db('t_group_info') -> where($map) -> delete();
        return $res;
    }
    /**
     * 获取产线信息通过ID
     */
    public function getGroupInfo($group_id)
    {
        $map['f_id'] = $group_id;
        $res = Db('t_group_info') -> where($map) -> find();
        return $res;
    }
}