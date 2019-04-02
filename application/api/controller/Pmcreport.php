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
class Pmcreport extends Base
{
    public function _empty($name)
    {
        //把所有城市的操作解析到city方法
        echo("<h2>对不起,开发人员偷懒啦（T﹏T）</h2>");
        echo("<h2>这个开发人员是个逗比!</h2>");
        echo("<h2>他没有写这个方法</h2>");
    }
    /**
     * 获取公司ID下得所有订单相关信息
     * api/Pmcreport/getAllOrderInfo
     */
    public function getAllOrderInfo()
    {
        if(request()->isAjax())
        {
            $company_id = input('c_id');
            
            $pmc_order_model = new PmcOrderModel();
            $insert_order_id = $pmc_order_model -> insertOrderInfo();


            // api_return([

            // ], 0, "OK");
        }
        else
        {
            api_return([], 1, "不是ajax请求");
        }
    }
    /**
     * 获取刚刚创建的订单ID下得所有工单相关信息
     * api/Pmcreport/getAllWorkOrderByOrderID
     */
    public function getAllWorkOrderByOrderID()
    {
        if(request()->isAjax())
        {
            $order_id = input('order_id');
            
            $pmc_order_model = new PmcOrderModel();
            $res_reponse_work_order = $pmc_order_model -> getAllWorkOrderInfo($order_id);
            api_return([
                'work_order_list' => $res_reponse_work_order
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "不是ajax请求");
        }
    }
}