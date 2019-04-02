<?php
namespace app\api\model;
use \think\Model;
class T_fault_event extends Model
{
    protected static function init()
    {
        // echo ('T_fault_event Model init!!-----');
    }
    //筛选数据
    public function getErrorFromDevice($device_uuid,$start_time,$end_time)
    {
        //构建查询条件
        $map['f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['f_device_uuid'] = $device_uuid;
        $map['f_stop_time'] = array('gt',0);
        
        $search_res = Db('t_fault_event')->field(array(
            'max(f_stop_time) as stop_time',
            'min(f_start_time) as start_time',
        ))  ->field('f_code') -> field('f_data') -> field('f_gateway_id')  ->where($map) ->group('f_code,f_stop_time') ->select();
        return $search_res;
    }
    //通过错误码查询错误的次数
    public function getErrorTimeByCode($device_uuid,$error_code,$start_time,$end_time)
    {
        $map['f_code'] = $error_code;
        $map['f_device_uuid'] = $device_uuid;
        $map['f_uploadtime'] = array('between',array($start_time,$end_time));
        $map['f_stop_time'] = array('gt',0);
        $search_res = Db('t_fault_event')->where($map) ->group('f_stop_time') ->select();
        return $search_res;
    }
}