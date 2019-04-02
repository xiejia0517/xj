<?php
namespace app\api\controller;

use \think\Controller;
use \think\Request;
use \think\Validate;

class Libsmt extends Controller
{

    /**
     * 批量生成 设备ID
     * /api/libsmt/generate_device_id
     *
     */
    public function generate_device_id()
    {
        $db = db("zdd_libsmt_device");
        $time = time();
        $count = 100;
        $device_id_start = "SMT305000000";
        $datas = [];
        $result = $db->field("device_id")->order("device_id DESC")->find();
        if($result){
            $device_id_start = $result["device_id"];
        }
        for ($i=0; $i<$count; $i++) {
            $device_id = ++$device_id_start;
            if($db->where("device_id='{$device_id}'")->count()<1){
                $datas[] = [
                    "device_id" => $device_id,
                    "device_key" => get_random(16, null, "ABCDEFGHJKMNPQRTUVWXY3456789"),
                    "time" => $time
                ];
            }
        }
        $result = $db->insertAll($datas);
        api_return([
            "count" => $result,
            "time" => $time
        ], 0, "操作成功");
    }


    /**
     * 批量生成 网关ID
     * /api/libsmt/generate_gateway_id
     *
     */
    public function generate_gateway_id()
    {
        $db = db("zdd_libsmt_gateway");
        $time = time();
        $count = 100;
        $gateway_id_start = "SMT500000000";
        $datas = [];
        $result = $db->field("gateway_id")->order("gateway_id DESC")->find();
        if($result){
            $gateway_id_start = $result["gateway_id"];
        }
        for ($i=0; $i<$count; $i++) {
            $gateway_id = ++$gateway_id_start;
            if($db->where("gateway_id='{$gateway_id}'")->count()<1){
                $datas[] = [
                    "gateway_id" => $gateway_id,
                    "gateway_key" => get_random(16, null, "ABCDEFGHJKMNPQRTUVWXY3456789"),
                    "time" => $time
                ];
            }
        }
        $result = $db->insertAll($datas);
        api_return([
            "count" => $result,
            "time" => $time
        ], 0, "操作成功");
    }
}
