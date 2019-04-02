<?php
namespace app\api\model;
use \think\Model;
use think\Db;
class Company extends Model
{
    protected static function init()
    {
        
    }
   
    //钩子函数

    //钩子函数

    //通过member_id查询所创建的全部公司信息
    public function getCompanyFromMemberID($member_id)
    {
        $res = Db('company') ->where('c_owner_id',$member_id) ->select();
        return $res;
    }

    //查询company数据表是否已经存在
    public function issetCompanyName($company_name)
    {
        $search_res = Db('company') ->field('c_name') ->where('c_name',$company_name) -> find();
        return $search_res;
    }
    //写入company数据库
    public function insertCompany($data)
    {
       $db_response = $this->insertGetId($data);
       return $db_response;
    }
    
    public function getCompanySingleres($company_id)
    {
        $company_single = Db('company') ->find($company_id);
        return $company_single;
    }
    public function getCompanySimpleres($company_id)
    {
        $companyres = Db('company') ->where('c_id',$company_id) ->find();
        $companyres_part = [
            'c_id' => $companyres['c_id'],
            'c_name' => $companyres['c_name'],
            'c_ads' => $companyres['c_ads'],
        ];
        return $companyres_part;
    }

    public function getCompanySingle($company_id)
    {
        $companyRes_arr = array();
        $device_count = 0;
        $companySingle = Db('company') ->where('c_id',$company_id) ->find();
        $device_res = $this->getDeviceRes($company_id);
        // dump($device_res);die;
        //遍历全部设备统计设备总数
        foreach ($device_res as $key => $value) {
            // dump($value['d_device_count']);
            $device_count += $value['d_device_count'];
        }
        $companySingle['c_device_count'] = $device_count;
        $companyRes_arr['company_info'] = $companySingle;
        $companyRes_arr['device_info'] = $device_res;

        return $companyRes_arr;
    }
    public function getDeviceRes($company_id)
    {
        //通过公司ID查询 其下属所有设备数据
        $com_device_res = Db('company_device')->where('d_company_id',$company_id)->select();
        return $com_device_res;
    }
    //获取公司下所有的产线数据集
    public function getGorupInfo($company_id)
    {
        $grounp_res = Db('t_group_info') ->where('f_company_id',$company_id) ->select();
        return $grounp_res;
    }
    //获取单一产线下所有设备数据集
    public function getDeviceInfo($group_id)
    {
        $device_fromgroup_res = Db('t_device')  ->where('f_line_id',$group_id) ->order('f_index asc') -> select();
        return $device_fromgroup_res;
    }
    //获取base_board和board
    public function getDeviceBoardInfoFromStartTime($device_uuid,$start_time,$end_time)
    {
        //构建查询条件
        $map['dd.f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['dd.f_device_uuid'] = $device_uuid;
        $map['dd.f_start_time'] = array('gt',0);
        //按照模组ID分组并返回每个模组smt_count的最大最小值
        $search_res = Db('t_device_data')->alias('dd')->join('t_task_name tn','dd.f_task_id = tn.f_id') ->field(array(
            'max(f_base_boardcount) as max_base_boardcount',
            'min(f_base_boardcount) as min_base_boardcount',
            'max(f_boardcount) as max_boardcount',
            'min(f_boardcount) as min_boardcount',
            'max(f_effective_time) as max_effective_time',
            'min(f_effective_time) as min_effective_time',
            'max(f_stop_time) as max_stop_time',
        ))  -> field('f_task_id') ->field('f_start_time') ->field('tn.f_task_name')  ->where($map) ->group('f_start_time') ->select();
        return $search_res;
    }
    //获取单一设备对象指定时间段内的信息结果集
    public function getDeviceInfoinTime($device_uuid,$start_time,$end_time)
    {
        //构建查询条件
        $map['f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['f_device_uuid'] = $device_uuid;
        //按照模组ID分组并返回每个模组smt_count的最大最小值
        $search_res = Db('t_device_data') ->cache(60)->field(array(
            'max(f_smt_count) as maxcount',
            'min(f_smt_count) as mincount',
            'max(f_base_boardcount) as max_base_boardcount',
            'min(f_base_boardcount) as min_base_boardcount',
            'max(f_boardcount) as max_boardcount',
            'min(f_boardcount) as min_boardcount', 
            'max(f_effective_time) as max_effective_time',
            'min(f_effective_time) as min_effective_time',
            // 'max(f_start_time) as max_start_time',
        ))  ->field('f_module_id') -> field('f_device_uuid')  ->where($map) ->group('f_module_id,f_start_time') ->select();
        return $search_res;
    }
    //获取单一设备task_id合并的结果集
    public function getDeviceGroupByTask($device_uuid,$start_time,$end_time)
    {
        //构建查询条件
        $map['dd.f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['dd.f_device_uuid'] = $device_uuid;
        $map['dd.f_start_time'] = array('gt',0);
        //按照模组ID分组并返回每个模组smt_count的最大最小值
        $search_res = Db('t_device_data')->alias('dd')->join('t_task_name tn','dd.f_task_id = tn.f_id') ->field(array(
            'max(f_smt_count) as maxcount',
            'min(f_smt_count) as mincount',
            'max(f_base_boardcount) as max_base_boardcount',
            'min(f_base_boardcount) as min_base_boardcount',
            'max(f_boardcount) as max_boardcount',
            'min(f_boardcount) as min_boardcount',
            'max(f_effective_time) as max_effective_time',
            'min(f_effective_time) as min_effective_time',
            'max(f_stop_time) as max_stop_time',
            'max(dd.f_uploadtime) as max_uploadtime',
        ))  -> field('f_task_id') ->field('f_start_time') ->field('dd.f_module_id') ->field('dd.f_device_uuid') ->field('tn.f_task_name')  ->where($map) ->group('f_task_id,f_start_time') ->select();
        return $search_res;
    }
    //通过最大的uploadtime去查询对应的记录中的stoptime
    public function getStopTimeByMaxUploadTime($max_upload_time)
    {
        $res = Db('t_device_data') ->field(array(
            'max(f_stop_time) as f_stop_time'
        )) ->where('f_uploadtime',$max_upload_time) ->group('f_uploadtime') ->select();
        return $res;
    }
    //获取某一设备的模组数量
    public function getDeviceModelCount($device_uuid)
    {
        // $map['f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['f_device_uuid'] = $device_uuid;
        $count_res = Db('t_device_data') ->where($map) ->group('f_module_id') ->select();
        return count($count_res);
    }
    //获取单一设备的任务的不同开始时间
    public function getDeviceStartTime($device_uuid,$start_time,$end_time)
    {
        //构建查询条件
        $map['f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['f_device_uuid'] = $device_uuid;
        $search_start_time = Db('t_device_data') -> field('f_task_id,f_start_time,f_device_uuid')  ->where($map) ->group('f_start_time') ->select();
        return $search_start_time;
    }
    //获取单一设备最新的start_time
    public function getNewestStartTime($device_uuid,$start_time,$end_time)
    {
        //构建查询条件
        $map['f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['f_device_uuid'] = $device_uuid;
        $new_start_time = Db('t_device_data') -> field('max(f_start_time) as max_start_time')  ->where($map) ->find();
        return $new_start_time;
    }
    //通过start_time获取task_id
    public function getTaskIdByStartTime($start_time)
    {
        $task_id = Db('t_device_data') ->field('f_task_id') ->where('f_start_time',$start_time) ->find();
        return $task_id;
    }
    //通过task_id获取task_name
    public function getTaskNameByTaskId($task_id)
    {
        $task_name = Db('t_task_name') ->field('f_task_name') ->where('f_id',$task_id) -> find();
        return $task_name;
    }
    //获取设备别名
    public function getDevieName($device_uuid)
    {
        $device_name = Db('t_device') -> where('f_device_uuid',$device_uuid) ->find();
        return $device_name;
    }
    //获取开启定时任务的公司ID
    public function getCrontabCompanyId()
    {
        $res = Db('company') ->field('c_id') ->where('c_crontab',1) ->select();
        return $res;
    }
    //写入中间数据t_cap_tmp
    public function insertCapTmp($data)
    {
        $res_insert = Db('t_cap_tmp') ->insertAll($data);
        return $res_insert;
    }
    //获取稼动数据
    public function getCompanyCropmobility($group_id,$start_time,$end_time)
    {
        $map['end_time'] = array('between',array($start_time,$end_time));
        $map['group_id'] = $group_id;
        $res = Db('t_cap_tmp') ->where($map) ->group('end_time') ->select();
        return $res;
    }
    //预留
    public function getTaskInfo($device_uuid,$start_time,$end_time)
    {
        //构建查询条件
        $map['f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['f_device_uuid'] = $device_uuid;
        $res_from_uuid = Db('t_task_name') -> where($map) ->group('f_task_name') ->select();
        return $res_from_uuid;
    }
}