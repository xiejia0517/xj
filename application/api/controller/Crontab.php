<?php
namespace app\api\controller;

use \think\Controller;
use \think\Request;
use \think\Validate;
use \think\Db;

class Crontab extends Controller
{

    /**
     * 定时任务 每分钟定时转化数据
     * 将原始累计的数据转化为点间数据
     * /api/crontab/device_data_transform
     *
     */
    public function device_data_transform()
    {
        // 读取原始数据
        $res_old_data = Db::table("ds_t_device_data")
            ->field("f_id,f_gw_id,f_deviceid,f_task_id,f_create_time,f_base_boardcount,f_boardcount,f_smt_count,f_start_time")
            ->where("f_create_time>".(int) db("zdd_device_data")->max("CreateTime"))
            ->limit(10) //
            ->order("f_create_time ASC")
            ->select();
        $datas = [];
        foreach ($res_old_data as $row) {
            // 查找device_id的上一条数据
            $row_last = Db::table("ds_t_device_data")
                ->where("f_gw_id='".$row["f_gw_id"]."' AND f_deviceid='".$row["f_deviceid"]."' AND f_create_time<'".$row["f_create_time"]."'")
                ->order("f_create_time DESC")
                ->find();
            // 是否接着上一条数据
            $follow = $row_last && ($row["f_create_time"]-$row_last["f_create_time"])<120; //  && ($row_last["f_start_time"]==)
            // 数据是否累加
            // $accumulation =
            //dump($follow);die;
            // 新数据
            $datas[] = [
                "GatewayID"     => $row["f_gw_id"],
                "DeviceID"      => $row["f_deviceid"],
                "TaskID"        => $row["f_task_id"],
                "OriginalID"    => $row["f_id"],
                "CreateTime"    => $row["f_create_time"],
                "BaseBoardCount"=> $follow ? ($row["f_base_boardcount"]-$row_last["f_base_boardcount"]) : $row["f_base_boardcount"],
                "BoardCount"    => $follow ? ($row["f_boardcount"] - $row_last["f_boardcount"]) : $row["f_boardcount"],
                "SmtCount"      => $follow ? ($row["f_smt_count"] - $row_last["f_smt_count"]) : $row["f_smt_count"],
                "DataStartTime" => $follow ? $row_last["f_create_time"] : $row["f_start_time"],
                "DataWorkSecond"=> $follow ? ($row["f_create_time"]-$row_last["f_create_time"]) : ($row["f_start_time"]-$row["f_create_time"]),
            ];
        }
        print_r($datas);
        $result = db("zdd_device_data")->insertAll($datas);
        api_return([
            "count" => $result
        ], 0, "OK");
    }
    /*
     * 数据表结构
    CREATE TABLE `ds_zdd_device_data` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '转化数据表->ID',
      `GatewayID` varchar(32) NOT NULL DEFAULT '' COMMENT '网关ID',
      `DeviceID` varchar(32) NOT NULL DEFAULT '' COMMENT '设备ID',
      `TaskID` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
      `BaseBoardCount` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT '生产基板数量',
      `BoardCount` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT '生产基板(电路)',
      `SmtCount` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '贴片数',
      `OriginalID` bigint(20) NOT NULL DEFAULT '0' COMMENT '原始数据ID',
      `DataStartTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据开始时间',
      `DataWorkSecond` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '数据工作秒数',
      `CreateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数据采集时间',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8
     */



}
