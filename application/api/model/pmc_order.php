<?php
namespace app\api\model;
use \think\Model;
use think\Db;
class Pmc_order extends Model
{
    protected static function init()
    {
        // echo ('T_group_info Model init!!-----');
    }
    /**
     * 插入之前验证是否有重复记录
     */
    public function checkWorkOrder($wo_number)
    {
        $res = Db('pmc_work_order') -> where('wo_number',$wo_number) ->find();
        if($res)
        {
            return 1;//已经有记录
        }
        else
        {
            return 0;//没有记录
        }
    }   
    /**
    * 写入提交的订单信息
    */
    public function insertOrderInfo($data)
    {
        $insert_order_id = Db('pmc_order') -> insertGetId($data);
        return $insert_order_id;
    }
    /**
     * 写入提交的工单信息
     */
    public function insertWorkOrderInfo($data)
    {
        $insert_work_order = Db('pmc_work_order') ->insertAll($data);
        return $insert_work_order;
    }
    /**
     * 获取OrderID下得所有workOrder
     */
    public function getAllWorkOrderInfo($order_id)
    {
        $res = Db('pmc_work_order') -> where('order_id',$order_id) ->select();
        return $res;
    }
    /**
     * 订单-工单-group建立联系
     */
    public function workOrderToGroupRelation($order_id,$wo_number,$group_id)
    {
        $map['order_id'] = $order_id;
        $map['wo_number'] = $wo_number;
        $data = [
            'group_id' => $group_id
        ];
        $res = Db('pmc_work_order') -> where($map) -> update($data);
        return $res ; //$res是update操作影响的行数
    }
}