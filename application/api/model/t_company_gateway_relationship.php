<?php
namespace app\api\model;
use \think\Model;
class T_company_gateway_relationship extends Model
{
    protected static function init()
    {
        // echo ('t_company_gateway_relationship Model init!!-----');
    }
    //通过提交的网关名查找网关ID
    public function getGatewayIdFromName($gateway_name)
    {
        $search_res = Db('t_gateway') -> where('f_username',$gateway_name) ->find();
        return $search_res;
    }
    //检测网关是否已经被绑定
    public function isUsed($gateway_id)
    {
        //判断对应的网关是否被使用
        $search_res = Db('t_company_gateway_relationship') ->where('f_gateway_id',$gateway_id) ->find();
        if($search_res)
        {
            return 1;//网关已经被使用
        }
        else
        {
            return 0;//网关未被使用
        }
    }
    //绑定网关到相应的公司
    public function creatRelationGatewayToCom($company_id,$gateway_id)
    {
        $data = [
            'f_company_id' => $company_id,
            'f_gateway_id' => $gateway_id,
        ];
        $creat_res = Db('t_company_gateway_relationship') -> insert($data);
        return $creat_res;
    }
    /**
     * 通过company_id查找所有的网关
     */
    public function getGatewayFromCID($company_id)
    {
        $search_res = Db('t_company_gateway_relationship') ->where('f_company_id',$company_id) ->select();
        return $search_res;
    }
}