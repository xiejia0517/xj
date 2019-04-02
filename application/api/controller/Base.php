<?php
namespace app\api\controller;

use think\Controller;
use think\Session;
use think\Cookie;
use think\Db;

/*
 * 模板基类
 */
class Base extends Controller
{
    public function _initialize()
    {
        // $sql = "
        // CREATE TABLE IF NOT EXISTS `ds_t_tttttttt` (
        //     `f_device_niname` varchar (192),
        //     `f_device_type` int (11),
        //     `f_device_uuid` varchar (192),
        //     `f_dmode` varchar (96),
        //     `f_dsub_version` varchar (192),
        //     `f_gateway_id` bigint (20),
        //     `f_id` bigint (20),
        //     `f_index` tinyint (4),
        //     `f_line_id` tinyint (4),
        //     `f_remark` varchar (192),
        //     `f_soft_version` varchar (96),
        //     `register_time` timestamp 
        // ); ";
        // $gateways = Db::query($sql);
    }
}