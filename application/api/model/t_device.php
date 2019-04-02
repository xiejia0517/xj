<?php
namespace app\api\model;
use \think\Model;
class T_device extends Model
{
    protected static function init()
    {
        // echo ('t_device Model init!!-----');
    }
    /**
     * 网关下所有未分线的设备
     */
    public function getDeviceUnline($gateway_id)
    {
        $map['f_gateway_id'] = $gateway_id;
        $map['f_line_id'] = 0;
        $search_res = Db('t_device') ->where($map) ->select();
        return $search_res;
    }
    /**
     * 更新分组信息和位置索引信息
     */
    public function updateGroupAndIndex($group_id,$device_uuid,$device_index)
    {
        $map['f_device_uuid'] = $device_uuid;
        $data = [
            'f_line_id' => $group_id,
            'f_index' => $device_index,
        ];
        $res = Db('t_device') ->where($map) ->update($data);
        return $res;
    }
}