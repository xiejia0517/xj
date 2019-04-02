<?php
namespace app\api\controller;

use \app\mobile\controller\MobileHome;
use think\Controller;
use think\Session;
use think\Db;
use app\api\model\member as MemberModel;
use app\api\model\member_relationship as MemRelationModel;
use app\api\model\company as CompanyModel;
use app\admin\controller\Type;
use app\api\model\t_company_gateway_relationship as TcompanyGatewayRModel;
use app\api\model\t_device as TdeviceModel;
use app\api\model\t_fault_event as TfaultEventModel;
use app\api\model\pmc_order as PmcOrderModel;
class Pmcreceive extends Base
{
    public function _empty($name)
    {
        //把所有城市的操作解析到city方法
        echo("<h2>对不起,开发人员偷懒啦（T﹏T）</h2>");
        echo("<h2>这个开发人员是个逗比!</h2>");
        echo("<h2>他没有写这个方法</h2>");
    }
    /**
     * 异步存储创建的订单信息
     * api/Pmcreceive/CreatNewOrder
     */
    public function CreatNewOrder()
    {
        if(request()->isAjax())
        {
            //接收提交的数据
            $product_name = input('product_name');
            $order_number = input('order_number');
            //构建数据库字段,写入数据
            $data = [
                'product_name' => $product_name,
                'order_number' => $order_number,
            ];
            $pmc_order_model = new PmcOrderModel();
            $insert_order_id = $pmc_order_model -> insertOrderInfo($data);

            api_return([
                'order_id' => $insert_order_id
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "不是ajax请求");
        }
    }
    /**
     * 异步创建工单信息,并和订单建立联系
     * api/Pmcreceive/CreatNewWorkOrder
     */
    public function CreatNewWorkOrder()
    {
        if(request()->isAjax())
        {
            //接收提交的数据
            $work_order_list = input('post.');
            $pmc_order_model = new PmcOrderModel();
           
            $data_list = [];
            
            // dump($work_order_list['order_arr'][1]['order_type_name']);
                for ($i=0; $i < $work_order_list['order_type'] ; $i++) { 
                    $check_res = $pmc_order_model -> checkWorkOrder($work_order_list['order_arr'][$i]['wo_number']);
                    if(0 == $check_res)
                    {
                        $data = [
                            'wo_number' => $work_order_list['order_arr'][$i]['wo_number'],
                            'board_type' => $work_order_list['order_arr'][$i]['board_type'],
                            'order_type_name' => $work_order_list['order_arr'][$i]['order_type_name'],
                            'board_count' => $work_order_list['order_arr'][$i]['board_count'],
                            'chips_count' => $work_order_list['order_arr'][$i]['chips_count'],
                            'start_time' => $work_order_list['order_arr'][$i]['start_time'],
                            'end_time' => $work_order_list['order_arr'][$i]['end_time'],
                            'priority' => $work_order_list['order_arr'][$i]['priority'],
                            'charging_time' => $work_order_list['order_arr'][$i]['charging_time'],
                            'first_check_time' => $work_order_list['order_arr'][$i]['first_check_time'],
                            'order_id' => $work_order_list['order_id'],
                        ];
                        $data_list [] = $data;
                    }
                    else
                    {
                        $data_list = null;
                    }
                }
                // dump($data_list);
                if($data_list != null)
                {
                    $insert_work_order = $pmc_order_model -> insertWorkOrderInfo($data_list);
                    api_return([], 0, "OK");
                }
                else
                {
                    api_return([], 1, "工单号重复,请重新录入!");
                }
        }
        else
        {
            api_return([], 1, "不是ajax请求");
        }
    }
}