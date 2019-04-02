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
class Autodata extends Controller
{
    /**
     * 排线
     * 获取公司网关下未排线设备信息和已经分线数据api:
     * api/mobilereport/getDeviceOngateway
     */
    public function getDeviceOngateway()
    {
        $company_id = input('company_id');
        if($company_id)
        {
            //正确接收到提交的company_id
            $gateway_relation_model = new TcompanyGatewayRModel();
            $device_model = new TdeviceModel();
            $company_model = new CompanyModel();

            $response_gateway = $gateway_relation_model -> getGatewayFromCID($company_id);
            $response_group = $company_model -> getGorupInfo($company_id);
            //遍历每一个网关ID获取未分线设备
            $unline_device_list = array();
            $group_list = array();
            foreach ($response_gateway as $key => $value) {
                $response_unlien_device_res = $device_model -> getDeviceUnline($value['f_gateway_id']);
                foreach ($response_unlien_device_res as $key => $value) {
                    $unline_device = [
                        'f_device_uuid' => $value['f_device_uuid'],
                        'f_device_niname' => $value['f_device_niname'],
                        'f_dmode' => $value['f_dmode'],
                        'f_line_id' => $value['f_line_id'],
                        'f_gateway_id' => $value['f_gateway_id'],
                    ];
                    $unline_device_list [] = $unline_device;
                }
            }
            //获取全部产线信息和产线中的设备信息
            foreach ($response_group as $key => $value) {
                $response_device_line_arr = $company_model -> getDeviceInfo($value['f_id']);
                // $device_arr = array();
                // foreach ($response_device_line_arr as $key => $v) {
                //     $line_device = [
                //         'device_uuid' => $v['f_device_uuid'],
                //         'device_niname' => $v['f_device_niname'],
                //         'device_dmode' => $v['f_dmode'],
                //         'device_line_id' => $v['f_line_id'],
                //     ];
                //     $device_arr [] = $line_device;
                // }
                $group_arr = [
                    'group_id' => $value['f_id'],
                    'group_name' => $value['f_group_name'],
                    'device_arr' => $response_device_line_arr,
                ];
                $group_list [] = $group_arr;
            }
            // dump($response_group);die;
            api_return([
                'device_unline_list' => $unline_device_list,
                'group_list' => $group_list,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "参数错误");
        }
    }
    
    /**
     * 注册界面调用的ajax接口api:
     * api/mobilereport/ajaxCompanyRegist
     */
    public function ajaxCompanyRegist()
    {
        if(input('company_name'))
        {
            //接收用户填写的公司昵称
            $company_name = input('company_name');
            //数据库核对是否存在
            $com_model = new CompanyModel();
            $isset_company_res = $com_model -> issetCompanyName($company_name);
            if(!$isset_company_res)
            {
                api_return([], 0, "company_name can use");
            }
            else
            {
                api_return([], 1, "公司名称已被注册");
            }
        }
        else
        {
            api_return([], 1, "parameter error");
        }
    }


    /**
     * 注册界面调用的全部注册公司列表:
     * api/mobilereport/getRegistCompanyList
     */
    public function getRegistCompanyList()
    {
        $session_get = (Session::get('member_id'));
        //判断Session
        if(isset($session_get))
        {
            //创建company数据模型对象
            $company_model = new CompanyModel();
            $reponse_res = $company_model -> getCompanyFromMemberID($session_get);

            api_return([
                "company_list"   => $reponse_res,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未登陆(Session不存在)");
        }
    }
    /**
     * 我的-公司列表-注册和关注的-加标识
     * api/mobilereport/getCompanyAllList
     */
    public function getCompanyAllList()
    {
        $session_get = (Session::get('member_id'));
        //判断Session
        if(isset($session_get))
        {
            //公司数据数组
            $regiest_company_arr = array();
            $unregiest_company_arr = array();
            $session_get = (Session::get('member_id'));
            $mem_model = new MemberModel();
            $com_model = new CompanyModel();
            $res_isregiest = $mem_model->getRelationIsRegiest($session_get,1);//传1表示查询注册的
            $res_unregiest = $mem_model->getRelationIsRegiest($session_get,0);//传0表示查询注册的
            // dump($res);die;
            foreach ($res_isregiest as $key => $value) {
                $com_single_info = $com_model -> getCompanySimpleres($value['company_id']);
                $role_arr = explode("|",$value['role']);
                $com_single_info['role'] = $role_arr;
                $regiest_company_arr[] = $com_single_info;
                
            }
            foreach ($res_unregiest as $key => $value) {
                $com_single_info = $com_model -> getCompanySimpleres($value['company_id']);
                $role_arr = explode("|",$value['role']);
                $com_single_info['role'] = $role_arr;
                $unregiest_company_arr[] = $com_single_info;
                
            }
            // dump($company_arr);die;
            api_return([
                "regiest_company_list"   => $regiest_company_arr,
                "follow_company_list"   => $unregiest_company_arr,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未登陆(Session不存在)");
        }
    }
    /**
     * company初级信息api:
     * api/mobilereport/getCompanySimpleInfo
     */
    public function getCompanySimpleInfo()
    {
        $session_get = (Session::get('member_id'));
        //判断Session
        if(isset($session_get))
        {
            //公司数据数组
            $company_arr = array();
            $session_get = (Session::get('member_id'));
            $mem_model = new MemberModel();
            $com_model = new CompanyModel();
            $res = $mem_model->getRelationship($session_get);
            foreach ($res as $key => $value) {
                $com_single_info = $com_model -> getCompanySimpleres($value['company_id']);
                $role_arr = explode("|",$value['role']);
                $com_single_info['role'] = $role_arr;
                $company_arr[] = $com_single_info;
                
            }
            // dump($company_arr);die;
            api_return([
                "company_list"   => $company_arr,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未登陆(Session不存在)");
        }
    }
    /**
     * 获取当前公司全部信息
     * api/mobilereport/getCompanyFullInfo
     */
    public function getCompanyFullInfo()
    {
        if(input('c_id'))
        {
            $company_arr = array();
            $c_id_receive = input('c_id');
            $com_model = new CompanyModel();
            $com_single =  $com_model -> getCompanySingleres($c_id_receive); 
            $com_single_deviceres = $com_model -> getDeviceRes($c_id_receive);
            
            //计算当前company拥有的设备总数
            $device_count = 0;
            foreach ($com_single_deviceres as $key => $value) {
                // dump($value['d_device_count']);
                $device_count += $value['d_device_count'];
            }
            $com_single['c_device_count'] = $device_count;

            api_return([
                'companyInfo' => $com_single,
                'device_list' => $com_single_deviceres,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未获取请求参数 c_id ");
        }
    }
    /*
    * 获取company信息接口(暂时不用)
    * api/mobilereport/getMobilerepost
    */
    public function getMobilerepost()
    {
        // $MemberInfo = $this->getMemberInfo();
        // print_r($this->MemberInfo['member_id']);die;
        // $memID = $this->MemberInfo['member_id'];
        //获取Session判断Session
        $session_get = (Session::get('member_id'));
        //判断Session
        if(isset($session_get))
        {
            
            //公司数据数组
            $company_arr = array();
            //创建member_relation模型对象
            $mem_model = new MemberModel();
            $com_model = new CompanyModel();
            $res = $mem_model->getRelationship($session_get);
            // echo(count($res));
            //遍历用户数据集合获取每一条对应的company公司信息
            foreach ($res as $key => $value) {
                $com_res = $com_model->getCompanySingle($value['company_id']);
                $company_arr [] = $com_res;
            }
            // dump($company_arr);die;
            api_return([
                "company_info_list"   => $company_arr,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未登陆(Session不存在)");
        }
    }
    /**
     * 获取已登录会员账号信息api
     * api/mobilereport/getMemberInfo
     */
    public function getMemberInfo()
    {
        $session_get = (Session::get('member_id'));
        //判断Session
        if(isset($session_get))
        {
            $mem_model = new MemberModel();
            $mem_s_info = $mem_model -> getSingleInfomation($session_get);
            //过滤字段数据
            $mem_need = [
                'member_id' => $mem_s_info['member_id'],
                'member_name' => $mem_s_info['member_name'],
                'member_truename' => $mem_s_info['member_truename'],
                'member_email' => $mem_s_info['member_email'],
                'member_mobile' => $mem_s_info['member_mobile'],
                'member_qq' => $mem_s_info['member_qq'],
                'member_avatar' => $mem_s_info['member_avatar'],
            ];
            api_return([
                "memberInfomation"   => $mem_need,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未登陆(Session不存在)");
        }
    }
    /**
     * 获取产能信息api
     * api/mobilereport/ProductivityInfo
     */
    public function ProductivityInfo()
    {
        if(input('c_id'))
        {
            $company_arr = array();
            $c_id_receive = input('c_id');
            $start_time = input('start_time');
            $end_time = input('end_time');

            $com_model = new CompanyModel();
            $company_search_res = $com_model -> getGorupInfo($c_id_receive);
            
            //遍历公司下每天产线分组获取单一产线组的设备数据集合
            $chips_sum = 0;//产线总数
            $base_boardcount = 0;//载板总数
            $board_count = 0;//电路板总数
            $task_files_kinds = array();
            foreach ($company_search_res as $key => $value) {
                $device_search_res = $com_model -> getDeviceInfo($value['f_id']);
                //遍历某一产线下所有设备获取单一设备的相关信息 查询目标:ds_t_device_data
                    foreach ($device_search_res as $key => $value) {
                        $device_data_res =  $com_model -> getDeviceInfoinTime($value['f_device_uuid'],$start_time,$end_time);
                        //遍历全部模组返回值计算差值然后合并
                        foreach ($device_data_res as $key => $value) {
                            $chips_sum += ($value['maxcount'] - $value['mincount']);
                            $base_boardcount += ($value['max_base_boardcount'] - $value['min_base_boardcount']);
                            $board_count += ($value['max_boardcount'] - $value['min_boardcount']);
                            // dump($value);
                        }
                    }
            }
            // dump($chips_sum);
            api_return([
                'chips_total' => $chips_sum,
                'baseboard_total' => $base_boardcount,
                'board_total' => $board_count,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未获取请求参数 c_id ");
        }
    }
    /**
     * 获取生产曲线api
     * api/mobilereport/ProductionCurve
     */
    public function ProductionCurve()
    {
        if(input('c_id'))
        {
            $company_arr = array();
            $c_id_receive = input('c_id');
            $start_time = input('start_time');
            $end_time = input('end_time');

            $com_model = new CompanyModel();
            //取得时间节点数
            $time_roots = $this->splitTime($start_time,$end_time);
            $production_arr = array();
            $production_data = [];
            //遍历公司下每天产线分组获取单一产线组的设备数据集合
            //根据时间节点数循环获取每个节点的相关信息
            for ($i=0; $i < $time_roots; $i++) { 
                $start_time_new = ($start_time+$i*3600);
                $end_time_new = ($start_time+($i+1)*3600);
                //每次循环获取一次节点信息
                $production = 0;
                $company_search_res = $com_model -> getGorupInfo($c_id_receive);
                foreach ($company_search_res as $key => $value) {
                    $device_search_res = $com_model -> getDeviceInfo($value['f_id']);
                    //遍历某一产线下所有设备获取单一设备的相关信息 查询目标:ds_t_device_data
                        foreach ($device_search_res as $key => $value) {
                            
                            $device_data_res =  $com_model -> getDeviceInfoinTime($value['f_device_uuid'],$start_time_new,$end_time_new);
                            //遍历全部模组返回值计算差值然后合并
                            foreach ($device_data_res as $key => $value) {
                                $production += ($value['maxcount'] - $value['mincount']);
                            }
                        }
                }
                $production_data = [
                    'start_time' => $start_time_new,
                    'end_time' => $end_time_new,
                    'production' => $production,
                ];
                $production_arr[] = $production_data;
                // dump('start_time:'.($start_time+$i*3600).' end_time'.($start_time+($i+1)*3600));
                // dump($effective_time_arr);
            }
            api_return([
                'production_arr' => $production_arr,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未获取请求参数 c_id ");
        }
    }
     /**
     * 获取产出时间曲线api
     * api/mobilereport/EffectiveTimeCurve
     */
    public function EffectiveTimeCurve()
    {
        if(input('c_id'))
        {
           
            $company_arr = array();
            $c_id_receive = input('c_id');
            $start_time = input('start_time');
            $end_time = input('end_time');

            $com_model = new CompanyModel();
            //取得时间节点数
            $time_roots = $this->splitTime($start_time,$end_time);
            $effective_time_arr = array();
            $effective_time_data = [];
            //遍历公司下每天产线分组获取单一产线组的设备数据集合
            
            //根据时间节点数循环获取每个节点的相关信息
            $company_search_res = $com_model -> getGorupInfo($c_id_receive);
            for ($i=0; $i < $time_roots; $i++) { 
                $start_time_new = ($start_time+$i*3600);
                $end_time_new = ($start_time+($i+1)*3600);
                //每次循环获取一次节点信息
                $effective_time = 0;
                
                foreach ($company_search_res as $key => $value) {
                    $device_search_res = $com_model -> getDeviceInfo($value['f_id']);
                    
                    //遍历某一产线下所有设备获取单一设备的相关信息 查询目标:ds_t_device_data
                        foreach ($device_search_res as $key => $value) {
                            $device_data_res =  $com_model -> getDeviceInfoinTime($value['f_device_uuid'],$start_time_new,$end_time_new);
                            //遍历全部模组返回值计算差值然后合并
                            foreach ($device_data_res as $key => $value) {
                                $effective_time += ($value['max_effective_time'] - $value['min_effective_time']);
                            }
                        }
                }
                $effective_time_data = [
                    'start_time' => $start_time_new,
                    'end_time' => $end_time_new,
                    'effective_time' => $effective_time,
                ];
                $effective_time_arr[] = $effective_time_data;
                // dump('start_time:'.($start_time+$i*3600).' end_time'.($start_time+($i+1)*3600));
                // dump($effective_time_arr);
            }
           
            api_return([
                'effective_arr' => $effective_time_arr,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未获取请求参数 c_id ");
        }
    } 

    /**
     * 获取线体情报api
     * api/mobilereport/ProductionlineInfo
     */
    public function ProductionlineInfo()
    {
        if(input('c_id'))
        {
            $company_arr = array();
            $c_id_receive = input('c_id');
            $start_time = input('start_time');
            $end_time = input('end_time');

            $com_model = new CompanyModel();
            $company_search_res = $com_model -> getGorupInfo($c_id_receive);
            
            //遍历公司下每天产线分组获取单一产线组的设备数据集合
            $productionline = array();
            foreach ($company_search_res as $key => $value) {
                $production_res = [];
                $chips_sum = 0;//产线总数
                $base_boardcount = 0;//载板总数
                $board_count = 0;//电路板总数
                $effective_time = 0;//工时数
                $production_res = [
                    'production_group_id' => $value['f_id'],
                    'produciton_group_name' => $value['f_group_name'],
                ];
                $device_search_res = $com_model -> getDeviceInfo($value['f_id']);
                //遍历某一产线下所有设备获取单一设备的相关信息 查询目标:ds_t_device_data
                $task_count_arr = array();
                $task_start_time_arr = array();
                    foreach ($device_search_res as $key => $value) {
                        $device_data_res =  $com_model -> getDeviceInfoinTime($value['f_device_uuid'],$start_time,$end_time);
                        $task_info = $com_model -> getDeviceStartTime($value['f_device_uuid'],$start_time,$end_time);
                        
                        $new_start_time_info = $com_model -> getNewestStartTime($value['f_device_uuid'],$start_time,$end_time);
                        $task_count_arr[] = count($task_info);
                        $task_start_time_arr [] = $new_start_time_info['max_start_time'];

                        //遍历全部模组返回值计算差值然后合并
                        foreach ($device_data_res as $key => $value) {
                            
                            $chips_sum += ($value['maxcount'] - $value['mincount']);
                            $base_boardcount += ($value['max_base_boardcount'] - $value['min_base_boardcount']);
                            $board_count += ($value['max_boardcount'] - $value['min_boardcount']);
                            $effective_time += ($value['max_effective_time'] - $value['min_effective_time']);
                        }

                    }
                if(sizeof($task_start_time_arr) != 0)
                {
                    $get_start_time = max($task_start_time_arr);
                }
                else
                {
                    $get_start_time = 0;
                }
                $task_id = $com_model -> getTaskIdByStartTime($get_start_time);
                $task_name = $com_model -> getTaskNameByTaskId($task_id['f_task_id']);
                $production_res ['chips_total'] = $chips_sum;
                $production_res ['baseboard_total'] = $base_boardcount;
                $production_res ['board_total'] = $board_count;
                $production_res ['effective_time'] = $effective_time;
                $production_res ['current_task_name'] = $task_name['f_task_name'];
                
                if(sizeof($task_count_arr) != 0)
                {
                    $production_res ['task_count'] = max($task_count_arr);
                }
                else
                {
                    $production_res ['task_count'] = 0;
                }
                // $production_res ['task_count'] = max($task_count_arr);
                
                $productionline [] = $production_res;
            }
            // dump($productionline);
            api_return([
                'productionline_arr' => $productionline,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未获取请求参数 c_id ");
        }
    }

    /**
     * 获取设备情报api
     * 通过提交的产线group_id获取该产线下每个设备的信息
     * api/mobilereport/DeviceFromGroupInfo
     */
    public function DeviceFromGroupInfo()
    {
        if(input('group_id'))
        {
            $company_arr = array();//预留
            $group_id_receive = input('group_id');
            $start_time = input('start_time');
            $end_time = input('end_time');
            //创建Company数据模型对象
            $com_model = new CompanyModel();
            
                $group_res_arr = array();
                //获取提交的group_id下得设备结果集
                $device_search_res = $com_model -> getDeviceInfo($group_id_receive);
                
                //遍历某一产线下所有设备获取单一设备的相关信息 查询目标:ds_t_device_data
                $task_count_arr = array();
                    foreach ($device_search_res as $key => $value) {
                        //获取设备别名
                        $device_b_name = $com_model -> getDevieName($value['f_device_uuid']);
                        //获取指定时间内单一设备的不同任务数量
                        $getDeviceStartTime = $com_model -> getDeviceStartTime($value['f_device_uuid'],$start_time,$end_time);

                        $per_device_res = array();
                        $chips_sum = 0;//产线总数
                        $base_boardcount = 0;//载板总数
                        $board_count = 0;//电路板总数
                        $effective_time = 0;//工时数
                        
                        $device_data_res =  $com_model -> getDeviceInfoinTime($value['f_device_uuid'],$start_time,$end_time);
                        
                        //遍历全部模组返回值计算差值然后合并
                        foreach ($device_data_res as $key => $value) {
                            
                            $chips_sum += ($value['maxcount'] - $value['mincount']);
                            $base_boardcount += ($value['max_base_boardcount'] - $value['min_base_boardcount']);
                            $board_count += ($value['max_boardcount'] - $value['min_boardcount']);
                            $effective_time += ($value['max_effective_time'] - $value['min_effective_time']);
                        }
                        //单一设备遍历结束统计信息
                        $per_device_res ['chips_total'] = $chips_sum;
                        $per_device_res ['baseboard_total'] = $base_boardcount;
                        $per_device_res ['board_total'] = $board_count;
                        $per_device_res ['effective_time'] = $effective_time;
                        $per_device_res ['device_name'] = $device_b_name['f_device_niname'];
                        $per_device_res ['device_model'] = $device_b_name['f_dmode'];
                        $per_device_res ['task_count'] = count($getDeviceStartTime);
                        $per_device_res ['device_uuid'] = $value['f_device_uuid'];

                        $group_res_arr [] = $per_device_res;
                    }
                // dump($group_res_arr);
            
            api_return([
                'group_res_arr' => $group_res_arr,
            ], 0, "OK");
        }
        else
        {
            api_return([], 1, "未获取请求参数 group_id ");
        }
    }

    /**
     * 获取当前group产线列表api
     * 通过提交的产线group_id获取该产线下的任务列表
     * api/mobilereport/TaskListInfo
     */
    public function TaskListInfo()
    {
        if(!input('device_uuid'))//如果查询请求没带device_uuid参数表示查询设备组任务列表
        {
                if(input('group_id'))
            {
                $company_arr = array();//预留
                $group_id_receive = input('group_id');
                $start_time = input('start_time');
                $end_time = input('end_time');
                //创建Company数据模型对象
                $com_model = new CompanyModel();
                $device_res_retun_arr = [];
                //通过group_id获取产线下得设备结果集
                $device_res_perGroup = $com_model ->getDeviceInfo($group_id_receive);//得到当前产线下有设备数结果集
                $group_chips_res = 0;
                $baseboard_res = 0;
                //遍历设备数量结果集
                foreach ($device_res_perGroup as $key => $value) {
                    //通过每次遍历得到的设备ID获取相关信息
                    $task_single_res_arr = array();
                    $device_res_retun = $com_model -> getDeviceGroupByTask($value['f_device_uuid'],$start_time,$end_time);
                    
                        foreach ($device_res_retun as $key => $value) {
                            
                            $chips_sum = ($value['maxcount'] - $value['mincount']);
                            $base_boardcount = ($value['max_base_boardcount'] - $value['min_base_boardcount']);
                            $board_count = ($value['max_boardcount'] - $value['min_boardcount']);
                            
                                $per_device_res ['chips_total'] = $chips_sum;
                                $per_device_res ['baseboard_total'] = $base_boardcount;
                                $per_device_res ['board_total'] = $board_count;
                                $per_device_res ['task_id'] = $value['f_task_id'];
                                $per_device_res ['task_name'] = $value['f_task_name'];
                            
                                $group_chips_res += $chips_sum;
                                $baseboard_res += $base_boardcount;
                                
                                $task_single_res_arr [] = $per_device_res;
                                // dump($task_single_res_arr);
                        }
                    
                    // dump($task_single_res_arr);
                    $device_res_retun_arr [] = $task_single_res_arr;
                }
                // dump($group_chips_res . 'task1');
                // dump($baseboard_res . 'task1');
                // dump($device_res_retun_arr);die;
                //将分组结果集调用合并任务算法进行数据合并
                $new_arr_return = $this -> mergePerSingleDevice($device_res_retun_arr,'task_id');
                
                $task_info_arr = $this -> mergeTask($device_res_retun_arr);
                api_return([
                    'task_info_arr' => $task_info_arr,
                ], 0, "OK");

            }
            else
            {
                api_return([], 1, "未获取请求参数 group_id ");
            }
        }
        else
        {   //请求中带有device_uuid参数表示查询单一设备任务列表
            $device_uuid = input('device_uuid');
            $start_time = input('start_time');
            $end_time = input('end_time');
            //创建Company数据模型对象
            $com_model = new CompanyModel();
            $device_single_return = $com_model ->getDeviceGroupByTask($device_uuid,$start_time,$end_time);
            // dump($device_single_return);die;
            //获取到设备的任务分组结果集进行遍历计算
            $task_info_arr = array();
            foreach ($device_single_return as $key => $value) {
                $per_device_result['chips_total'] = ($value['maxcount'] - $value['mincount']);
                $per_device_result['baseboard_total'] = ($value['max_base_boardcount'] - $value['min_base_boardcount']);
                $per_device_result['board_total'] = ($value['max_boardcount'] - $value['min_boardcount']);
                $per_device_result ['task_name'] = $value['f_task_name'];
                $per_device_result ['task_id'] = $value['f_task_id'];

                $task_info_arr [] = $per_device_result;
            }
            api_return([
                'task_info_arr' => $task_info_arr,
            ], 0, "OK");
        }
    }

    /**
     * 在查询结果集中合并单一设备的同任务ID的数据
     */
    public function mergePerSingleDevice($arr,$task_id)
    {
        //定义一个新的数组用于接收重组并返回
        $new_arr = array();
        //遍历结果集$value就是一个单独的设备
        foreach ($arr as $key => $value) {
            $new_arr_info = array();
            $new_arr_temp = array();
            $index = 0;
            foreach ($value as $k => $v) {
                // dump($v);
                // $new_arr_single = array();
                if(!in_array($v[$task_id],$new_arr_temp,true))
                {
                    $new_arr_temp = $v;
                    $new_arr_info [] = $new_arr_temp;
                    $index++;
                }
                else
                {
                    $new_arr_info[$index-1]['chips_total'] += $v['chips_total'];
                    $new_arr_info[$index-1]['baseboard_total'] = $v['baseboard_total'];
                    $new_arr_info[$index-1]['board_total'] = $v['board_total'];
                }
                // $new_arr_temp [] = $new_arr_single;
            }//遍历结束得到一个设备的全部任务结果集
            // dump($new_arr_info);
            
        }
        return $new_arr;
    }
    /**
     * 合并任务数据算法
     */
    public function mergeTask($res_arr)
    {
        // dump($res_arr);die;
        $device_number = count($res_arr);//内存循环 设备数
        $task_number = count($res_arr[0]);//外层循环 任务数
        // dump($device_number. '设备数');die;
        // dump($task_number . '任务数');
        $res_arr_return = array();
        for ($i=0; $i < $task_number; $i++) { 
            
            
            $all_device_chips = 0;
            $all_device_base_board = 0;
            $all_device_board = 0;
            for ($j=0; $j <$device_number ; $j++) { 
                if(empty($res_arr[$j][$i]))
                {
                    // dump('不存在');
                    $all_device_chips += 0;
                    $all_device_base_board += 0;
                    $all_device_board += 0;
                }
                else
                {
                    $all_device_chips += $res_arr[$j][$i]['chips_total'];
                    $all_device_base_board += $res_arr[$j][$i]['baseboard_total'];
                    $all_device_board += $res_arr[$j][$i]['board_total'];
                }
            }
            

            $single_task_res = [
                'chips_total' => $all_device_chips,
                'all_device_base_board' => $all_device_base_board,
                'all_device_board' => $all_device_board,
                'task_name' => $res_arr[0][$i]['task_name'],
            ];
            // dump($single_task_res);
            $res_arr_return [] = $single_task_res;
        }
        return ($res_arr_return);
    }

    /**
     * 通过用户选择的时间进行时间节点转换
     */
    public function splitTime($start_time,$end_time)
    {
        //已经小时为一个节点
        $current_time = time();
        $time_root = ($end_time - $start_time) / 3600;
        return intval(ceil($time_root));
    }
    public function test_info()
    {
        
        $device_uuid = input('device_uuid');
        $start_time = input('start_time');
        $end_time = input('end_time');

        $map['dd.f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['dd.f_device_uuid'] = $device_uuid;

        $search_res = Db('t_device_data')->alias('dd')->join('t_task_name tn','dd.f_task_id = tn.f_id') -> field('f_task_id') ->field('tn.f_task_name')  ->where($map) ->group('f_task_id') ->select();
        foreach ($search_res as $key => $value) {
            // dump($value);
        }
    }

    public function test_info_res()
    {
        
        $device_uuid = input('device_uuid');
        $start_time = input('start_time');
        $end_time = input('end_time');

        $map['dd.f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['dd.f_device_uuid'] = $device_uuid;

        $search_res = Db('t_device_data')->alias('dd') -> field('f_smt_count') ->where($map) ->select();
        // foreach ($search_res as $key => $value) {
        //     dump($value);
        // }
        // dump($search_res);
    }
    /**
     * 测试当天数据接口
     * 通过提交的产线group_id获取该产线下的任务列表
     * api/lmjport/testcheck
     */
    public function testcheck()
    {
        return view();
    }
    //临时测试封装方法(合并task_id)
    public function mergePerSingleDeviceTemp($arr,$task_id)
    {
        // dump($arr);die;
        //定义一个新的数组用于接收重组并返回
        $new_arr = array();
        //遍历结果集$value就是一个单独的设备
        foreach ($arr as $key => $value) {
            dump($value);die;
            $new_arr_info = array();
            // $new_arr_temp = array();
            $new_arr_temp = [
                "chips_total" => 0,
                "baseboard_total" => 0,
                "board_total" => 0,
                "device_uuid" => '',
                "task_id" => 0,                
                "module_id" => 0,
                "task_name" => '',
                "start_time" => '',
                "start_time_unix" => 0,
                "stop_time" => '',
                "stop_time_unix" => 0,
             ];
            $index = 0;
            foreach ($value as $k => $v) {
                // dump($v);
                // $new_arr_single = array();
                if(!in_array($v[$task_id],$new_arr_temp,true))
                {
                    
                    $new_arr_temp = $v;
                    $new_arr_info [] = $new_arr_temp;
                    // dump($new_arr_info);die;
                    $index++;
                }
                else
                {
                    $new_arr_info[$index-1]['chips_total'] += $v['chips_total'];
                    $new_arr_info[$index-1]['baseboard_total'] += $v['baseboard_total'];
                    $new_arr_info[$index-1]['board_total'] += $v['board_total'];
                    $new_arr_info[$index-1]['stop_time'] = $v['stop_time'];
                }
                // $new_arr_temp [] = $new_arr_single;
            }//遍历结束得到一个设备的全部任务结果集
            // dump($new_arr_info);
            $new_arr [] = $new_arr_info;
        }
        // dump($new_arr);die;


        $this -> mergePerSingleDevieModule($new_arr,'device_uuid','module_id');
        return $new_arr;
    }
    //临时测试封装方法(合并模组)
    public function mergePerSingleDevieModule($arr,$device_uuid,$module_id)
    {
        //定义一个新的数组用于接收重组并返回
        $new_arr = array();
        //遍历结果集$value就是一个单独的设备
        foreach ($arr as $key => $value) {
            // dump($value);die;
            $new_arr_info = array();
            // $new_arr_temp = array();
            $new_arr_temp = [
                "chips_total" => 0,
                "baseboard_total" => 0,
                "board_total" => 0,
                "device_uuid" => '',
                "task_id" => 0,
                "module_id" => 0,
                "task_name" => '',
                "start_time" => '',
                "start_time_unix" => 0,
                "stop_time" => '',
                "stop_time_unix" => 0,
            ];
            $index = 0;
            foreach ($value as $k => $v) {
                //  dump($v);
            
                // $new_arr_single = array();
                if(in_array($v[$device_uuid],$new_arr_temp,true) && !in_array($v[$module_id],$new_arr_temp,true))
                {
                    // $new_arr_info[$index-1]['chips_total'] += $v['chips_total'];
                    // $new_arr_info[$index-1]['baseboard_total'] = $v['baseboard_total'];
                    // $new_arr_info[$index-1]['board_total'] = $v['board_total']; 
                    
                }
                else
                {
                    $new_arr_temp = $v;
                    $new_arr_info [] = $new_arr_temp;
                //  dump($new_arr_info);
                    $index++; 
                }
                // $new_arr_temp [] = $new_arr_single;
            }//遍历结束得到一个设备的全部任务结果集
            // dump($new_arr_info);
            $new_arr [] = $new_arr_info;
        }
        // dump($new_arr);die;
        $this->mergeSingleLine($new_arr);
        // return $new_arr;
    }
     //临时测试封装方法(合并最终产线生产总量)
     public function mergeSingleLine($arr)
     {
         //定义一个新的数组用于接收重组并返回
         $new_arr = array();
         //遍历结果集$value就是一个单独的设备
         $chips_total = 0;
         $baseboard_total = 0;
         $board_total = 0;
         $task_name = '';
         foreach ($arr as $key => $value) {
             // dump($value);die;
             foreach ($value as $k => $v) {
                //   dump($v);
                $chips_total += $v['chips_total'];
                $baseboard_total += $v['baseboard_total'];
                $board_total += $v['board_total'];
                 
                 // $new_arr_temp [] = $new_arr_single;
             }//遍历结束得到一个设备的全部任务结果集
            //  dump($new_arr_info);
            //  $new_arr [] = $new_arr_info;
         }
         $new_arr = [
            'chips_total' => $chips_total,
            'all_device_base_board' => $baseboard_total,
            'all_device_board' => $board_total,
            'task_name' => '',
        ];
        //  dump($new_arr);
        //  return $new_arr;
     }
    public function temptestfun($beginToday,$endToday,$group_id)
    {
        $group_id_receive = $group_id;
                $start_time = $beginToday;
                $end_time = $endToday;
                //创建Company数据模型对象
                $com_model = new CompanyModel();
                $device_res_retun_arr = [];
                //通过group_id获取产线下得设备结果集
                $device_res_perGroup = $com_model ->getDeviceInfo($group_id_receive);//得到当前产线下有设备数结果集
                // $device_res_model_count = $com_model ->getDeviceModelCount('41f2af52698653ae49ba6940781310e6');
                // dump($device_res_perGroup);die;
                $group_chips_res = 0;
                $baseboard_res = 0;
                //遍历设备数量结果集
                
                foreach ($device_res_perGroup as $key => $value) {
                    // dump($value);die;
                    //通过每次遍历得到的设备ID获取相关信息
                    $task_single_res_arr = array();
                    $device_res_retun = $com_model -> getDeviceGroupByTask($value['f_device_uuid'],$start_time,$end_time);
                    
                    
                        foreach ($device_res_retun as $key => $value) {
                            // dump($value);die;
                            $chips_sum = ($value['maxcount'] - $value['mincount']);
                            $base_boardcount = ($value['max_base_boardcount'] - $value['min_base_boardcount']);
                            $board_count = ($value['max_boardcount'] - $value['min_boardcount']);
                            $stop_res_retun = $com_model -> getStopTimeByMaxUploadTime($value['max_uploadtime']);
                                $per_device_res ['chips_total'] = $chips_sum;
                                $per_device_res ['baseboard_total'] = $base_boardcount;
                                $per_device_res ['board_total'] = $board_count;
                                $per_device_res ['task_id'] = $value['f_task_id'];
                                $per_device_res ['device_uuid'] = $value['f_device_uuid'];
                                $per_device_res ['module_id'] = $value['f_module_id'];
                                $per_device_res ['task_name'] = $value['f_task_name'];
                                $per_device_res ['start_time'] = date("Y-m-d H:i:s",$value['f_start_time']) ;
                                $per_device_res ['start_time_unix'] = $value['f_start_time'] ;
                                $per_device_res ['stop_time'] = date("Y-m-d H:i:s",$stop_res_retun[0]['f_stop_time']) ;
                                $per_device_res ['stop_time_unix'] = $stop_res_retun[0]['f_stop_time'] ;
                            
                                // $group_chips_res += $chips_sum;
                                // $baseboard_res += $base_boardcount;
                                
                                $task_single_res_arr [] = $per_device_res;
                                
                        }
                        // dump($task_single_res_arr);
                        
                    
                    // dump($task_single_res_arr);
                    $device_res_retun_arr [] = $task_single_res_arr;
                }
                // dump($group_chips_res . 'task1');
                // dump($baseboard_res . 'task1');
                // dump($device_res_retun_arr);
                //将分组结果集调用合并任务算法进行数据合并
                
                // $new_arr_return = $this -> mergePerSingleDeviceTemp($device_res_retun_arr,'task_id');
                return $device_res_retun_arr;
    }
    public function gettestcheck()
    {
        $start_time = strtotime(input('date'));//$time 某天的 00:00：00时间戳
        $end_time = $start_time + 86400 -1;
        $group_id = input('groupID');
        // $group_id = input('group');

        // dump($group_id);
        // dump($start_time);
        // dump($end_time);die;
            
            $this -> temptestfun($start_time,$end_time,$group_id );
    }
    public function ecfn()
    {
        return view();
    }
    public function timeline()
    {
        // 目的:获取用户输入的开始时间与结束时间与生产线且将时间转为时间戳
        // 注:若需转换为时间戳则先要去掉中文
        // 注:结束时间戳应该为结束时间当天的23:59:59分
        $res = input('post.');
        dump($res);
        if($res)
        {
            $in_start_time = $res['start'];
            $in_start_time = str_replace(array('年','月','日'),'/',$in_start_time);
            $in_start_time = strtotime($in_start_time);
            // $in_end_time = $res['end'];
            // $in_end_time = str_replace(array('年','月','日'),'/',$in_end_time);
            // $in_end_time = strtotime($in_end_time);
            $in_end_time = $in_start_time + 86400 -1;
            $group_id = $res['group'];
                $arr_list = $this -> temptestfun($in_start_time,$in_end_time,$group_id);
                // dump($arr_list);
                $uuid_arr = [];
                foreach($arr_list as $key => $arr){                 
                    foreach($arr as $value){
                        // dump($value);
                        // 提出需要的字段做处理
                        $uuid = $value['device_uuid'];
                        // dump(strlen($uuid));
                        $device = substr($uuid,28);
                        $device = $device.'设备';
                        $task_id = $value['task_id'];
                        $start = $value['start_time_unix'];
                        // 先查询task_id是否存在数据库，若存在并且时间小于查询当天最开始的时间，则说明该任务持续运行，将开始时间赋值为当天开始时间
                        $tasked = Db('zdd_analysis_data')
                        ->where($task_id)
                        ->find();
                        if($start < $in_start_time and $tasked)
                        {
                            $start = $in_start_time;
                        }

                        $end = $value['stop_time_unix'];
                        // 如果时间戳为0则将赋值给当天最后时间
                        if($end == 0)
                        {
                           $end = $in_end_time;
                        }
                        $smtline = '';
                        switch($group_id)
                        {
                            case 1:
                            $smtline = '一号线';
                            break;
                            case 2:
                            $smtline = '二号线';
                            break;
                            case 4:
                            $smtline = '三号线';
                            break;
                            case 14:
                            $smtline = '四号线';
                            break;
                        }
                        $task_arr = [];
                        $task_arr['device'] = $device;
                        $task_arr['task'] = $task_id;
                        $task_arr['start'] = $start;
                        $task_arr['end'] = $end;
                        $task_arr['smtline'] = $smtline;
                        $uuid_arr[] = $task_arr;
                        
                        // dump($uuid);
                        // dump($task_id);
                        // dump($task_arr);
                    }
                    // dump($uuid_arr);
                }
                dump($uuid_arr);
                foreach($uuid_arr as $key => $data_arr){
                    // dump($data_arr);die;
                    $map['start'] = $data_arr['start'];
                    $map['end'] = $data_arr['end'];
                    $map['task'] = $data_arr['task'];
                    $result = Db('zdd_analysis_data')
                    ->where($map)
                    ->find();
                    if($result){
                        echo '<h3>该数据已存在数据库</h3>';
                    }
                    else{
                        Db('zdd_analysis_data')
                        ->insert($data_arr);
                        echo '<h3>插入成功</h3>';
                    }
                    
                }
            // dump($start_time);      
            // dump($end_time);
        // dump($group_id);
        }
        return view();
    }
}