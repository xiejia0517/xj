<?php

namespace app\api\query;

use \think\Db;
use \think\Config;
use \app\mobile\controller\MobileHome;

class DeviceReport extends MobileHome
{

    // 分左右两边的设备
    private $GROUP_DEVICE = array("JUKI_FX_3L", "JUKI_FX_3RL");

    private $UNKNOWN_GROUP_NAME = "未知产线";

    // TODO: 使这个值按照实际的产线长度来确定
    private $ABNORMAL_THRESHOLD = 5; // 当一条产线所有在工作的设备中间的基板数最大差值超过这个阈值时，认为统计异常

    /*
     * 查询给定时间内，给定网关的所有任务id
     * device_sub_id 为设备子id，可选
     * 返回的字段名称为task_id, task_name
     */
    private function get_tasks_for_gateway($gateway_id, $start_time, $end_time, $device_sub_id = NULL)
    {
        $whereAnd = [];
        $whereAnd[] = "device.f_gw_id='{$gateway_id}'";
        $whereAnd[] = "device.f_start_time > 0";
        $whereAnd[] = "device.f_task_id = task.f_id";
        if ($device_sub_id) {
            $whereAnd[] = "task.f_device_id = {$device_sub_id}";
        }
        $whereAnd[] = "(device.f_create_time BETWEEN {$start_time} AND {$end_time})";
        $result = Db::table("ds_t_device_data device, ds_t_task_name task")
            ->field("f_task_id task_id, f_task_name task_name, device.f_deviceid device_sub_id")
            ->where(join($whereAnd, " AND "))
            ->group("f_task_id")
            ->select();
        return $result;
    }

    private function get_task_name($id)
    {
        $result = Db::table("ds_t_task_name")
            ->field("f_task_name")
            ->where("f_id = {$id}")
            ->find();
        return $result["f_task_name"];
    }

    private function get_line_name($gateway_id, $sub_id)
    {
        $result = Db::table("ds_t_gw_bind_device")
            ->field("f_group_name line")
            ->where("f_gw_id = '{$gateway_id}' AND f_device_id = {$sub_id}")
            ->find();
        $name = "";
        if (is_null($result["line"])) {
            $name = $this->UNKNOWN_GROUP_NAME;
        } else {
            $name = $result["line"];
        }
        return $name;
    }

    /*
    * 查询用户每一条的产线的其中一个网关，网关由group by选择
    * 返回的字段名称为gateway_id, line_name
    */
    private function get_random_gateway_for_each_line($user_id)
    {
        $sql = "SELECT relation.f_user2_name gateway_id, bind.f_group_name line_name
                FROM ds_t_user_relationship relation
                LEFT JOIN ds_t_gw_bind_device bind
                ON relation.f_user2_name = bind.f_gw_id
                WHERE relation.f_user1_name='{$user_id}' AND bind.f_device_id = 1";
        $gateways = Db::query($sql);
        return $gateways;
    }

    private function get_gateway_list($user_id)
    {
        $whereAnd = [];
        $whereAnd[] = "f_user1_name='{$user_id}'";
        $gateways = Db::table("ds_t_user_relationship")
            ->field("f_user2_name gateway_id")
            ->where(join($whereAnd, " AND "))
            ->select();
        $result = [];
        foreach ($gateways as $row) {
            $result[] = $row['gateway_id'];
        }
        return $result;
    }

    /*
     * 返回每个gateway的ID和产线名称（一个网关可以属于多条产线）
     */
    public function get_gateways($user_id)
    {
        $sql = "SELECT relation.f_user2_name gateway_id, bind.f_group_name line_name
                FROM ds_t_user_relationship relation
                LEFT JOIN ds_t_gw_bind_device bind
                ON relation.f_user2_name = bind.f_gw_id
                WHERE relation.f_user1_name='{$user_id}'";
        $query = Db::query($sql);
        $result = [];
        foreach ($query as $row_gw) {
            $gw = $row_gw["gateway_id"];
            $line = $row_gw["line_name"];
            if (!array_key_exists($gw, $result)) {
                $result[$gw] = [];
            }

            $name = "";
            if (!is_null($line)) {
                $name = $row_gw["line_name"];
            } else {
                $name = $this->UNKNOWN_GROUP_NAME;
            }
            if (!in_array($name, $result[$gw])) {
                $result[$gw][] = $name;
            }
        }

        // 重新组织数据结构, 使得给前端的接口更清晰明了
        $recap = [];
        foreach ($result as $gw => $lines) {
            $temp = [];
            $temp["id"] = $gw;
            $temp["line"] = $lines;
            $recap[] = $temp;
        }

        return $recap;
    }

    public function get_devices($user_id)
    {
        $whereAnd = [];
        $whereAnd[] = "f_user1_name='{$user_id}'";
        $whereAnd[] = "a.f_user2_name=b.f_gw_id";
        $devices = Db::table("ds_t_user_relationship a, ds_t_gw_bind_device b")
            ->field("f_user2_name gateway_id, f_dmode model_name, f_device_id sub_id, f_device_status online_status, f_group_name line")
            ->where(join($whereAnd, " AND "))
            ->select();

        $result = [];
        foreach ($devices as $device) {
            if (is_null($device["line"])) {
                $device["line"] = $this->UNKNOWN_GROUP_NAME;
            }

            // 对于分左右两边的设备, 如FX-3R, 将整体看成一台设备，所以过滤掉多余的设备
            if (!in_array($device["model_name"], $this->GROUP_DEVICE) || $device["sub_id"] == 1) {
                $result[] = $device;
            }
        }
        return $result;
    }

    /*
     * 获得用户的任务列表，可以指定一个生产的（注意不是任务创建的）时间区间
     * user_id, 用户的id
     * page, 指定页码. 若<=0则不分页, 返回所有内容
     * start_time, 时间区间的开始时间
     * end_time, 时间区间的结束时间
     */
    public function get_tasks($user_id, $page = 0, $start_time = NULL, $end_time = NULL)
    {
        // 因为数据库中数据表有不同的前缀，用ThinkPHP的数据库接口使用join方法是会报错，所以使用原生查询
        if ($start_time == NULL || $end_time == NULL) {
            $sql = "SELECT task.f_id task_id, f_gw_id gateway_id, f_device_id sub_id, f_task_name task_name, f_create_time create_time
                    FROM ds_t_task_name task
                    LEFT JOIN ds_t_user_relationship user
                    ON task.f_gw_id = user.f_user2_name
                    WHERE user.f_user1_name = '{$user_id}'
                    ORDER BY task_id";
        } else { // 考虑时间区间内有哪些任务有过生产, 这和t_task_name表中的f_create_time无关
            $sql = "SELECT t1.task_id, t2.gateway_id, t2.sub_id, t2.task_name, t2.create_time
                    FROM 
                    (
                      SELECT f_task_id task_id
                      FROM ds_t_device_data
                      WHERE f_create_time BETWEEN {$start_time} AND {$end_time}
                      GROUP BY f_task_id
                    ) t1
                    LEFT JOIN
                    (
                      SELECT task.f_id task_id, f_gw_id gateway_id, f_device_id sub_id, f_task_name task_name, f_create_time create_time
                      FROM ds_t_task_name task
                      LEFT JOIN ds_t_user_relationship user
                      ON task.f_gw_id = user.f_user2_name
                      WHERE user.f_user1_name = '{$user_id}'
                      ORDER BY task_id
                    ) t2
                    ON t1.task_id = t2.task_id";
        }
        $limit = "";
        if ($page > 0) {
            $page_num = Config::get("PAGE_NUMBER");
            $offset = $page_num * ($page - 1);
            $limit = "LIMIT {$offset}, {$page_num}";
        }
        $tasks = Db::query($sql . $limit);

        // 添加任务所在产线的名称
        $result = [];
        foreach ($tasks as $task) {
            // TODO: 这里会造成查询效率很低，是否可以优化？
            $task["line"] = $this->get_line_name($task["gateway_id"], $task["sub_id"]);
            $result[] = $task; // 上面的修改不会影响到$tasks, 所以需要重新创建一个列表
        }

        return $result;
    }

    /*
     * 以设备为单位统计数据。
     * 获取用户产线基板和电路板的统计数。分产线求总数,除非指定了网关ID
     * user_id, 用户的id
     * start_time, 查询的开始时间
     * end_time, 查询的结束时间
     * gateway_id, 网关id, 若为空则查询所有网关
     * device_sub_id, 设备子id, 若为空则查询网关下的所有设备. 如果gateway_id为空, 则该参数会被忽略
     * task_id, 任务id, 若gateway_id为空，则该参数会被忽略
     */
    public function get_count($user_id, $start_time, $end_time, $gateway_id = NULL, $device_sub_id = NULL, $task_id = NULL)
    {
        $devices = $this->get_devices($user_id);
        // total smt count
        $smt_count = 0;
        // for smt count
        $device_count0 = [];
        $task_count0 = [];
        // for baseboard count
        $device_count1 = [];
        $task_count1 = [];
        // for board count
        $device_count2 = [];
        $task_count2 = [];
        // 设备的产线信息
        $device_line = [];

        foreach ($devices as $device) {
            $whereAnd = [];
            $whereAnd[] = "f_start_time > 0";
            $whereAnd[] = "(f_create_time BETWEEN {$start_time} AND {$end_time})";
            if(!is_null($gateway_id)) {
                if ($device["gateway_id"] != $gateway_id) {
                    continue;
                }
                if (!is_null($device_sub_id) && $device["sub_id"] != $device_sub_id) {
                    continue;
                }
                if (!is_null($task_id)) {
                    $whereAnd[] = "f_task_id = {$task_id}";
                }
            }
            $whereAnd[] = "f_gw_id = '{$device['gateway_id']}'";
            $whereAnd[] = "f_deviceid = {$device['sub_id']}";
            $res_task = Db::table("ds_t_device_data")
                ->field("
                    MIN(f_smt_count) AS smt_min,
                    MAX(f_smt_count) AS smt_max,
                    MIN(f_base_boardcount) AS baseboard_min,
                    MAX(f_base_boardcount) AS baseboard_max,
                    MIN(f_boardcount) AS board_min,
                    MAX(f_boardcount) AS board_max, f_task_id task_id")
                ->where(join($whereAnd, " AND "))
                // 同一个任务也可能被清零以后重新开始，这时时候任务id不变，任务开始时间会变，所以这里考虑任务开始时间
                ->group("f_task_id, f_start_time")
                ->select();
            $count0 = 0; // smt count per device
            $count1 = 0; // baseboard count per device
            $count2 = 0; // board count per device
            foreach ($res_task as $row_task) {
                $v0 = $row_task["smt_max"] - $row_task["smt_min"];
                $v1 = $row_task["baseboard_max"] - $row_task["baseboard_min"];
                $v2 = $row_task["board_max"] - $row_task["board_min"];

                $smt_count += $v0;
                $count0 += $v0;
                $task = [];
                $task["id"] = $row_task["task_id"];
                $task["name"] = $this->get_task_name($task["id"]); // TODO: 这里会造成查询效率很低，是否可以优化？
                $task["count"] = $v0;
                $task_count0[] = $task;

                $count1 += $v1;
                $task = [];
                $task["id"] = $row_task["task_id"];
                $task["name"] = $this->get_task_name($task["id"]); // TODO: 这里会造成查询效率很低，是否可以优化？
                $task["count"] = $v1;
                $task_count1[] = $task;

                $count2 += $v2;
                $task = [];
                $task["id"] = $row_task["task_id"];
                $task["name"] = $this->get_task_name($task["id"]); // TODO: 这里会造成查询效率很低，是否可以优化？
                $task["count"] = $v2;
                $task_count2[] = $task;
            }
            $device_id = $device["gateway_id"]."-".$device["sub_id"];
            $device_count0[$device_id] = $count0;
            $device_count1[$device_id] = $count1;
            $device_count2[$device_id] = $count2;
            $device_line[$device_id] = $device["line"];
        }

        // 对于smt总数, 还需要考虑分左右两边的设备的另一边
        foreach ($devices as $device) {
            $target_subid = 2; // TODO, 考虑多余2个模块一组的设备
            if (in_array($device["model_name"], $this->GROUP_DEVICE) && $device["sub_id"] == 1) {
                $whereAnd = [];
                $whereAnd[] = "f_start_time > 0";
                $whereAnd[] = "(f_create_time BETWEEN {$start_time} AND {$end_time})";
                if(!is_null($gateway_id)) {
                    if ($device["gateway_id"] != $gateway_id) {
                        continue;
                    }
                    if (!is_null($device_sub_id) && $target_subid != $device_sub_id) {
                        continue;
                    }
                    if (!is_null($task_id)) {
                        $whereAnd[] = "f_task_id = {$task_id}";
                    }
                }
                $whereAnd[] = "f_gw_id = '{$device['gateway_id']}'";
                $whereAnd[] = "f_deviceid = 2";
                $res_task = Db::table("ds_t_device_data")
                    ->field("
                    MIN(f_smt_count) AS smt_min,
                    MAX(f_smt_count) AS smt_max, f_task_id task_id")
                    ->where(join($whereAnd, " AND "))
                    // 同一个任务也可能被清零以后重新开始，这时时候任务id不变，任务开始时间会变，所以这里考虑任务开始时间
                    ->group("f_task_id, f_start_time")
                    ->select();
                $count0 = 0; // smt count per device
                foreach ($res_task as $row_task) {
                    $v0 = $row_task["smt_max"] - $row_task["smt_min"];
                    $smt_count += $v0;
                    $count0 += $v0;
                    $task = [];
                    $task["id"] = $row_task["task_id"];
                    $task["name"] = $this->get_task_name($task["id"]); // TODO: 这里会造成查询效率很低，是否可以优化？
                    $task["count"] = $v0;
                    $task_count0[] = $task;
                }
                $device_count0[$device["gateway_id"]."-".$target_subid] = $count0;
            }
        }

        // 对于基板数和电路数，每个产线只计算其中一台的数据，取最大的值
        $baseboard_count = 0;
        $board_count = 0;
        // TODO: 下面的 line_count 是以产线的名称作为key，如果名称中包含空格会出问题，所以此处及其他相关的代码需要重新改造
        $line_count1 = []; // 每条线的 basebaord_count
        $line_count2 = []; // 每条线的 board_count
        $is_abnormal = []; // 记录产线是否有数据异常的情况
        foreach ($device_line as $device_id => $line_name) {
            $is_abnormal[$line_name] = false;
        }
        foreach ($device_line as $device_id => $line_name) {
            if (array_key_exists($line_name, $line_count1)) {
                // 更新基板数
                $current = $line_count1[$line_name];
                $new = $device_count1[$device_id];
                if ($new > $current) {
                    $line_count1[$line_name] = $new;
                }
                // 检测基板统计异常
                if (abs($current-$new) > $this->ABNORMAL_THRESHOLD) {
                    $is_abnormal[$line_name] = true;
                }
                // 更新电路数
                $current = $line_count2[$line_name];
                $new = $device_count2[$device_id];
                if ($new > $current) {
                    $line_count2[$line_name] = $new;
                }
            }
            else {
                $line_count1[$line_name] = $device_count1[$device_id];
                $line_count2[$line_name] = $device_count2[$device_id];
            }
        }
        foreach ($line_count1 as $count) {
            $baseboard_count += $count;
        }
        foreach ($line_count2 as $count) {
            $board_count += $count;
        }

        $result = [];
        $result["smt_count"] = $smt_count;
        $result["device_smt_count"] = $device_count0;
        $result["task_smt_count"] = $task_count0;
        $result["base_boardcount"] = $baseboard_count;
        $result["device_base_boardcount"] = $device_count1;
        $result["task_base_boardcount"] = $task_count1;
        $result["line_base_boardcount"] = $line_count1;
        $result["boardcount"] = $board_count;
        $result["device_boardcount"] = $device_count2;
        $result["task_boardcount"] = $task_count2;
        $result["line_boardcount"] = $line_count2;
        $result["is_abnormal_detected"] = $is_abnormal;
        return $result;
    }

    /*
     * 获取用户的贴片统计历史数据
     * user_id, 用户的id
     * start_time, 查询的开始时间
     * end_time, 查询的结束时间
     * gateway_id, 网关id, 若为空则查询所有网关
     * device_sub_id, 设备子id, 若为空则查询网关下的所有设备. 如果gateway_id为空, 则该参数会被忽略
     * task_id, 任务id, 若gateway_id为空，则该参数会被忽略
     */
    public function get_smt_histogram($user_id, $start_time, $end_time, $gateway_id = NULL, $device_sub_id = NULL, $task_id = NULL)
    {
        $gateways = $this->get_gateway_list($user_id);
        $result = [];
        $whereAndBase = [];
        $whereAndBase[] = "f_start_time > 0";
        $whereAndBase[] = "(f_create_time BETWEEN {$start_time} AND {$end_time})";
        $gateway_to_iterate = [];
        if ($gateway_id) {
            if (!in_array($gateway_id, $gateways)) {
                return 0; // 该用户名下没有要查询的网关
            }
            $gateway_to_iterate[] = $gateway_id;
            if ($device_sub_id) {
                $whereAndBase[] = "f_deviceid = {$device_sub_id}";
            }
        } else { // 查询所有网关
            $gateway_to_iterate = $gateways;
        }

        foreach ($gateway_to_iterate as $gateway) {
            $res_task = $this->get_tasks_for_gateway($gateway, $start_time, $end_time, $device_sub_id);
            foreach ($res_task as $row_task) {
                if ($task_id && $task_id != $row_task['task_id']) {
                    continue; // 指定了task，忽略掉所有其他task
                }
                $whereAnd = $whereAndBase;
                $whereAnd[] = "f_gw_id='{$gateway}'";
                $whereAnd[] = "f_task_id='{$row_task['task_id']}'";
                $whereAnd[] = "f_start_time > 0";
                $whereAnd[] = "f_create_time BETWEEN {$start_time} AND {$end_time}";
                $res_data = Db::table("ds_t_device_data")
                    ->field("f_create_time time, f_smt_count count")
                    ->where(join($whereAnd, " AND "))
                    ->select();
                $task_data = [];
                $task_data['gateway_id'] = $gateway;
                $task_data['device_sub_id'] = $row_task['device_sub_id'];
                $task_data['task_id'] = $row_task['task_id'];
                $task_data['task_name'] = $row_task['task_name'];

                // 将数据库结果转换为二维数组，便于前端直接用来展示曲线
                $data = [];
                foreach ($res_data as $row_data) {
                    $data[] = [$row_data['time'], $row_data['count']];
                }
                $task_data['data'] = $data;

                $result[] = $task_data;
            }
        }

        return $result;
    }

    /*
     * 获取用户每条产线的有效运行时间
     * user_id, 用户的id
     * start_time, 查询的开始时间
     * end_time, 查询的结束时间
     */
    // TODO, 需要重新考虑逻辑
    public function get_effective_time($user_id, $start_time, $end_time)
    {
        $gateways = $this->get_random_gateway_for_each_line($user_id);
        $result = [];
        foreach ($gateways as $gateway) {
            $whereAnd = [];
            $whereAnd[] = "f_start_time > 0";
            $whereAnd[] = "(f_create_time BETWEEN {$start_time} AND {$end_time})";
            $whereAnd[] = "f_gw_id = '{$gateway["gateway_id"]}'";
            $res_task = Db::table("ds_t_device_data")
                ->field("
                    MIN(f_effective_time) AS time_min,
                    MAX(f_effective_time) AS time_max")
                ->where(join($whereAnd, " AND "))
                // 同一个任务也可能被清零以后重新开始，这时时候任务id不变，任务开始时间会变，所以这里考虑任务开始时间
                ->group("f_task_id, f_start_time")
                ->select();
            $time_count = 0;
            foreach ($res_task as $row_task) {
                $time_count += $row_task["time_max"] - $row_task["time_min"];
            }
            $line_data = [];
            $line_data["line_name"] = $gateway["line_name"];
            $line_data["effective_time"] = $time_count;
            $result[] = $line_data;
        }
        return $result;
    }
}