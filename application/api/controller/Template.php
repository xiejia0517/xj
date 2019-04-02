<?php
namespace app\api\controller;

use \think\Controller;
use \think\Db;
use \think\Log;
use \think\Request;
//require_once 'app\api\Controller\Zcurl.php';
//require './Zcurl.php';
//use \think\Controller\Zcurl;


class Template extends Controller
{
    /*    public function _initialize() {
            parent::_initialize();
        }*/

    /**
     * 页面：首页
     * /api/template/qrcode
     *
     */
    public function qrcode()
    {
        import('qrcode.phpqrcode', EXTEND_PATH);
        $value = input('get.url');
        $value = isset($value) && $value!="" ? $value : "https://shop.zuoduoduo.cn/api/template/index.html";
        $errorCorrectionLevel = "L";
        $matrixPointSize = "4";
        \QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize,2);
        exit;
    }
    /**
     * 页面：首页
     * /api/template/index.html
     *
     */
    public function index()
    {
      return $this->fetch();
    }

    /**
     * 页面：新界面中的 旧数据页面
     * /api/template/mine_old.html
     *
     */
    public function mine_old()
    {
        return $this->fetch();
    }

    /**
     * 页面：设备列表
     * /api/template/device_list.html
     *
     */
    public function device_list()
    {
        return $this->fetch();
    }

    /**
     * 页面：告警页面
     * /api/template/alarm.html
     *
     */
    public function alarm()
    {
        return $this->fetch();
    }

    /**
     * 页面：生成工厂二维码
     * /api/template/create_company_qrcode.html
     *
     */
    public function create_company_qrcode()
    {
        $c_id = intval(input("id/d"));
        if($c_id<1)  $c_id = 2;
        $exptime = time() + 86400;
        $KEY_ENCODE = config("KEY_ENCODE");
        $url = "https://" . $_SERVER["SERVER_NAME"];
        $url .= "/zddtpl/index/factory_join.html?factory_id={$c_id}&exptime={$exptime}&key=".md5($c_id.$exptime.$KEY_ENCODE);
        return $this->fetch("create_company_qrcode", ["url"=>urlencode($url)]);
    }

    /**
     * 页面：设备分组列表
     * /api/template/group_list.html
     *
     */
    public function group_list(){
        $user_id        = "18603027764";//测试固定账号
        $where = "f_username='{$user_id}'";
        $user_group=DB::table('ds_t_user')->where($where)->find();
        $group_arr=json_decode($user_group['f_teaminfo'],true);//json转换数组
        //组装sql条件
        $whereAnd[] = "f_user1_name='{$user_id}'";
        $whereAnd[] = "a.f_user2_name=b.f_gw_id";
        $devices = Db::table("ds_t_user_relationship a, ds_t_gw_bind_device b")
            ->field("f_user2_name gateway_id, f_dmode model_name, f_device_id sub_id, f_device_status online_status,f_group_name group_name,b.f_id bid,f_sort")
            ->where(join($whereAnd, " AND "))
            ->group('f_dcpuid')
            ->order('f_sort asc')
            ->select();

        foreach($group_arr as &$v){
            $whereAnd['b.f_group_name'] =$v['teamname'];
            $v['num']=Db::table("ds_t_user_relationship a, ds_t_gw_bind_device b")
                ->field("f_user1_name,f_user2_name gateway_id, f_group_name,f_gw_id,f_dcpuid")
                ->where('f_user1_name',$user_id)
                ->where('f_group_name',$v['teamname'])
                ->where('f_user2_name=f_gw_id')
                ->group('f_dcpuid')
                ->count();
        }

//        $group_arr = array_column($group_arr,null,'no');  //版本升级后再用
        $group_arr = _array_column($group_arr,null);//版本升级前勿改动

        foreach($devices as $k=>$v){
            if (@array_key_exists($v['group_name'], $group_arr['group_name'])) {
                $devices_list[$k]=$v;
                $devices_list[$k]['group_name'] = $group_arr['group_name'];
            }
        }
        $this->assign('group_list',$group_arr);//全部分组
        $this->assign('devices_list',$devices);//用户下面的所有设备
        return $this->fetch();
    }

    /**
     * 页面：添加分组
     */
    public function addGroup(){
        $group_name=input('post.group_name');
        if($group_name==''){
            ds_json_encode(0,'分组名称不能为空');
        }
        $user_id        = "18603027764";
        $where = "f_username='{$user_id}'";
        $user_group=DB::table('ds_t_user')->where($where)->find();
        if($this->deep_in_array($group_name,json_decode($user_group['f_teaminfo'],true))){
            ds_json_encode(2,'分组名称不能重复');
        }
        $group_arr=json_decode($user_group['f_teaminfo'],true);
        $group_arr[]["teamname"] = $group_name;
        $jsondata=json_encode($group_arr,JSON_UNESCAPED_UNICODE);
        $save_data=Db::table('ds_t_user')->where($where)->setField('f_teaminfo', $jsondata);
        if($save_data){
            ds_json_encode(1,'添加成功');
        }else{
            ds_json_encode(0,'添加失败');
        }
    }

    /**
     * @param $value 字符串
     * @param $array 数组
     * @return bool
     */
    public function deep_in_array($value, $array){
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return $item;
                } else {
                    continue;
                }
            }

            if (in_array($value, $item)) {
                return $item;
            } else if ($this->deep_in_array($value, $item)) {
                return $item;
            }
        }
        return false;

    }

    /**
     * 页面：设备详细
     * /api/template/device_detail.html
     *
     */
    public function device_detail()
    {
        return $this->fetch();
    }

    /**
     * 页面：数据报告
     * /api/template/report_index.html
     *
     */
    public function report_index()
    {
        return $this->fetch();
    }

    /**
     * 页面: 设备详细数据以及分组信息
     * /api/template/device_group_detail
     */
    public function  device_group_detail(){
        $id=intval(input('param.f_id'));
        $user_id        = "18603027764";
        $where = "f_username='{$user_id}'";
        $user_group=DB::table('ds_t_user')->where($where)->find();
        $group_arr=json_decode($user_group['f_teaminfo'],true);

        $tmp_group_name = array();
        if (is_array($group_arr)) {
            foreach ($group_arr as $k => $v) {
                $tmp_group_name[$v['teamname']] = $v['teamname'];
            }
        }

        $whereAnd[] = "f_user1_name='{$user_id}'";
        $whereAnd[] = "a.f_user2_name=b.f_gw_id";
        $whereAnd[] = "b.f_id=$id";
        $devices = Db::table("ds_t_user_relationship a, ds_t_gw_bind_device b")
            ->field("f_user2_name gateway_id, f_dmode model_name, f_device_id sub_id, f_device_status online_status,f_group_name group_name,b.f_id bid,b.f_dsub_version,f_dcpuid,f_soft_version,f_sort")
            ->where(join($whereAnd, " AND "))
            ->find();
        $this->assign('devices_detail',$devices);
        $this->assign('group_name',$tmp_group_name);
        return $this->fetch();
    }

    public function groupEdit()
    {
        $group_name=input('post.f_group_name');
        $bid=input('post.bid');
        $f_sort=input('post.f_sort');
        $data['f_group_name']=$group_name;
        $data['f_sort']=$f_sort;
        $new_group_name=Db::table('ds_t_gw_bind_device')->where('f_id', $bid)->update($data);
        if($new_group_name){
            ds_json_encode(1,'修改分组成功');
        }else{
            ds_json_encode(0,'修改分组失败');
        }

    }

    public function groupRename()
    {
        $oldname=input('post.oldname');//原名
        $group_rname_save=input('post.group_rname_save');//新名称
        $user_id        = "18603027764";//测试固定账号
        $where = "f_username='{$user_id}'";
        $user_group=DB::table('ds_t_user')->where($where)->find();
        if($this->deep_in_array($group_rname_save,json_decode($user_group['f_teaminfo'],true))){
            ds_json_encode(2,'分组名称已存在');
        }
        $group_arr=json_decode($user_group['f_teaminfo'],true);//json转换数组
        foreach($group_arr as $k=>$v){
            if($v['teamname']==$oldname){
                unset($group_arr[$k]);
            }
        }

        $whereAnd[] = "f_user1_name='{$user_id}'";
        $whereAnd[] = "a.f_user2_name=b.f_gw_id";
        $whereAnd[] = "b.f_group_name='{$oldname}'";
        $group_arr[]["teamname"] = $group_rname_save;
        $group_arr = _array_column($group_arr,null);
        $jsondata=json_encode($group_arr,JSON_UNESCAPED_UNICODE);
        $save_data=Db::table('ds_t_user')->where($where)->setField('f_teaminfo', $jsondata);

        if($save_data){
            $new_group_name['b.f_group_name']=$group_rname_save;
            Db::table("ds_t_user_relationship a, ds_t_gw_bind_device b")
                ->where(join($whereAnd, " AND "))
                ->update($new_group_name);
            ds_json_encode(1,'修改成功');
        }else{
            ds_json_encode(0,'修改失败');
        }

    }

    /**
     * 执行删除分组操作
     */
    public function groupDel()
    {
        $group_teamname=input('post.group_teamname');
        $user_id        = "18603027764";//测试固定账号
        $where = "f_username='{$user_id}'";
        $user_group=DB::table('ds_t_user')->where($where)->find();

        $group_arr=json_decode($user_group['f_teaminfo'],true);//json转换数组
        foreach($group_arr as $k=>$v){
            if($v['teamname']==$group_teamname){
                unset($group_arr[$k]);
            }
        }

        $whereAnd[] = "f_user1_name='{$user_id}'";
        $whereAnd[] = "a.f_user2_name=b.f_gw_id";
        $group_arr = _array_column($group_arr,null);
        $jsondata=json_encode($group_arr,JSON_UNESCAPED_UNICODE);
        $save_data=Db::table('ds_t_user')->where($where)->setField('f_teaminfo', $jsondata);


        $whereAnd[] = "b.f_group_name='{$group_teamname}'";
        $new_group_name['b.f_group_name']='';
        $res=Db::table("ds_t_user_relationship a, ds_t_gw_bind_device b")
            ->where(join($whereAnd, " AND "))
            ->update($new_group_name);

        ds_json_encode(1,'删除成功');


    }
}
