<?php
namespace app\api\controller;

use \app\mobile\controller\MobileHome;
use think\Controller;
use think\Session;
use app\api\model\company as CompanyModel;
use app\api\model\member_relationship as MemRelationModel;
use app\api\model\t_company_gateway_relationship as TcompanyGatewayRModel;
use app\api\model\t_device as TdeviceModel;
use app\api\model\t_group_info as TgroupInfoModel;
use think\Request;
class Mobilereceive extends Controller
{

    /**
     * 删除关注用户
     * api/Mobilereceive/deleteFolMember
     */
    public function deleteFolMember()
    {
        $company_id = input('company_id');
        $group_id = input('member_id');
        if($group_id && $group_id)
        {
            //参数全部正确接收
            $mem_reloation_model = new MemRelationModel();
            
                
                $response_res = $mem_reloation_model -> delFollMember($company_id,$group_id);
                if($response_res == 0)
                {
                    api_return([], 1, "删除操作失败");
                }
                else
                {
                    api_return([], 0, "删除成功");
                }
        }
        else
        {
            api_return([], 1, "参数错误");
        }
    }

     /**
     * 更改用户角色权限
     * api/Mobilereceive/editRoleToMember
     */
    public function editRoleToMember()
    {
        $recive_post = input('post.');
        // dump($recive_post);die;
        if($recive_post)
        {
            // dump($recive_post);
            $member_relation_model = new MemRelationModel();
            //从提交的角色数组构建数据
            if(isset($recive_post['member_role_list']))
            {
                $new_role_str = '';
                foreach ($recive_post['member_role_list'] as $key => $value) {
                    $new_role_str .= $value . '|';
                }
                $rtrim_new_role_str = rtrim($new_role_str,'|');
            }
            else
            {
                $rtrim_new_role_str = '';
            }
            $uadate_res = $member_relation_model -> updateMemberRole($recive_post['company_id'],$recive_post['member_id'],$rtrim_new_role_str);
            if(1 == $uadate_res)
            {
                api_return([], 0, "更新成功");
            }
            else
            {
                api_return([], 1, "更新失败");
            }
        }
        else
        {
            api_return([], 1, "参数错误");
        }
    }

    /**
     * 删除产线
     * api/Mobilereceive/deleteNewGroup
     */
    public function deleteNewGroup()
    {
        $company_id = input('company_id');
        $group_id = input('group_id');
        if($group_id && $group_id)
        {
            //参数全部正确接收
            $group_info_model = new TgroupInfoModel();
            
                
                $response_res = $group_info_model -> deleteGroupInfo(1,12);
                if($response_res == 0)
                {
                    api_return([], 1, "删除操作失败");
                }
                else
                {
                    api_return([], 0, "删除产线成功");
                }
        }
        else
        {
            api_return([], 1, "参数错误");
        }
    }

    /**
     * 新建产线
     * api/Mobilereceive/editNewGroup
     */
    public function editNewGroup()
    {
        $company_id = input('company_id');
        $group_id = input('group_id');
        $group_name = input('group_name');
        if($company_id)
        {
            if($group_name)
            {
                //参数全部正确接收,写入数据库
                //group_id { 0 : 新建产线  非0 : 修改}
                $group_info_model = new TgroupInfoModel();
                if(0 == $group_id)
                {
                    //新建操作
                    $response_group_id = $group_info_model -> creatGroupInfo($company_id,$group_name);
                    $response_group_info = $group_info_model -> getGroupInfo($response_group_id);
                    api_return([
                        'group_info' => $response_group_info,
                    ], 0, "新建产线完成");
                }
                else
                {
                    //修改操作
                    $group_info_model -> editGroupInfo($company_id,$group_name,$group_id);
                    $response_group_info = $group_info_model -> getGroupInfo($group_id);
                    api_return([
                        'group_info' => $response_group_info,
                    ], 0, "修改产线完成");
                }
                // dump($company_id );
                // dump($group_id);
                // dump($group_name);
            }
            else
            {
                api_return([], 1, "名字不能为空");
            }
        }
        else
        {
            api_return([], 1, "companyID参数错误");
        }
    }

    /**
     * 排线操作
     * api/Mobilereceive/editGroupAndDevice
     */
    public function editGroupAndDevice()
    {
        $recive = input('post.');
        // dump($recive);
        if($recive)//正确获取参数并接收
        {
            //根据提交的信息更新数据表设备分组信息
            $device_model = new TdeviceModel();
            $group_info_model = new TgroupInfoModel();
            if($recive['from_group'] != 0)
            {
                //更新来源组的信息
                // dump($recive['from_sortkey']);
                foreach ($recive['from_sortkey'] as $key => $value) {
                    $respons_res = $device_model -> updateGroupAndIndex($recive['from_group'],$value['uuid'],$value['index']);
                    // dump($value['uuid']);
                }
            }
            //更新目标组的信息
            foreach ($recive['target_sortkey'] as $key => $value) {
                $respons_res = $device_model -> updateGroupAndIndex($recive['target_group'],$value['uuid'],$value['index']);
                // dump($value['uuid']);
            }
            api_return([], 0, "更改成功");
        }
        else
        {
            api_return([], 1, "请求参数错误");
        }
    }
    /**
     * 添加网关绑定关系
     * api/Mobilereceive/bindGatewayToCom
     */
    public function bindGatewayToCom()
    {
        //接收提交的company_id和gateway_name
        $recive_company_id = input('company_id');
        $recive_gateway_name = input('gateway_name');
        if($recive_company_id && $recive_gateway_name)//正确接收数据
        {   
            $gateway_relation_model = new TcompanyGatewayRModel();
            $search_gateway = $gateway_relation_model -> getGatewayIdFromName($recive_gateway_name);
            $check_gateway = $gateway_relation_model -> isUsed($search_gateway['f_id']);
            if($check_gateway == 0)
            {
                //完成绑定
                $response_bing_res = $gateway_relation_model -> creatRelationGatewayToCom($recive_company_id,$search_gateway['f_id']);
                $gateway_data = [
                    'bind_company_id' => $recive_company_id,
                    'gateway_id' => $search_gateway['f_id'],
                    'gateway_name' => $recive_gateway_name,
                ];
                if(1 == $response_bing_res)
                {
                    api_return([
                        'gateway_info' => $gateway_data,
                    ], 0, "绑定完成");
                }
                else
                {
                    api_return([], 1, "绑定失败");
                }
            }
            else
            {
                api_return([], 1, "网关已经被使用,请重新扫描");
            }
        }
        else//非正确获取提交数据
        {
            api_return([], 1, "提交参数错误");
        }
    }

    /**
     * 注册company
     * api/Mobilereceive/registCompany
     */
    public function registCompany()
    {   
        $session_get = (Session::get('member_id'));

        //判断Session
        if(isset($session_get))
        {
            //公司数据数组
            $company_name = input('company_name');
            $city_select = input('area');
            $getway_name = input('getway_name');
            $company_address = input('addr');
            $business_license_base64 = input('business_license_base64');
            /**
             * 校验前端提交的数据
             */
            if($company_name && $city_select && $getway_name && $company_address && $business_license_base64)
            {
                $city_res =explode("/",$city_select);
                //图片解析
                // "http://shop.zuoduoduo.cn/uploads/home/zhizhao/" . time() . ".png";
                $img_name = md5(uniqid(microtime(true),true));
                $saveUrl = "/uploads/home/common/" . $img_name. ".png";
                $uploadUrl =  ROOT_PATH . "/public/uploads/home/common/" . $img_name . ".png";
                 //写入文件并保存
                $company_data = [
                    'c_name' => $company_name,
                    'c_address' => $company_address,
                    'c_province' => $city_res[0],
                    'c_city' => $city_res[1],
                    'c_area' => $city_res[2],
                    'c_pic' => $saveUrl,
                    'c_owner_id' => $session_get,
                ];
    
                $gatway_data = [
                    'gateway_name' => $getway_name,
                ];
    
                // dump($company_data);die;
                // dump($gatway_data);die;
                // dump($member_data);
                
                //检测网关是否已被绑定,已经绑定返回错误,未绑定正常提交完成注册
                $gateway_relation_model = new TcompanyGatewayRModel();
                $search_gateway_id = $gateway_relation_model -> getGatewayIdFromName($gatway_data['gateway_name']);
                // dump($search_gateway_id['f_id']);die;
                $check_gateway = $gateway_relation_model -> isUsed($search_gateway_id['f_id']);
                // dump($check_gateway);die;
                if($check_gateway == 0)
                {
                    //网关可以注册,完成公司注册
                    $company_model = new CompanyModel();
                    $member_relationship = new MemRelationModel();
                    //二次验证公司名称是否重复
                    $isset_company_res = $company_model -> issetCompanyName($company_name);
                    // dump($isset_company_res);die;
                    if(!$isset_company_res)
                    {
                        $response_company_id = $company_model -> insertCompany($company_data);//将公司信息写入company数据表并获取company_ID
                        // dump($response_company_id);die;
                        //获取到company_id  将合法的网关与其进行绑定 写入关系表
                        $response_bind_gateway = $gateway_relation_model -> creatRelationGatewayToCom($response_company_id,$search_gateway_id['f_id']);
                        //将注册者与company建立联系写入关系表
                        $response_bind_member = $member_relationship ->createRelationMemberToCom($session_get,$response_company_id);
                        file_put_contents($uploadUrl, base64_decode($business_license_base64));
                        /////////////////////////
                        //插入完成返回注册完成的公司信息
                        $response_company_info = $company_model ->getCompanySingleres($response_company_id);
                        
                        api_return([
                            'company_id' => $response_company_info['c_id'],
                            'company_name' => $response_company_info['c_name'],
                        ], 0, "ok");
                    }
                    else
                    {
                        //网关已经被使用,返回错误,不进行注册
                        api_return([], 1, "公司名称已被注册");
                    }
                }
                else
                {
                    //网关已经被使用,返回错误,不进行注册
                    api_return([], 1, "网关已经被使用,请重新扫描");
                }
            }
            else
            {
                api_return([], 1, "参数错误");
            }
            
        }
        else
        {
            api_return([], 1, "未登陆(Session不存在)");
        }
    }

    //base64图片解析
    /**
     * [将Base64图片转换为本地图片并保存]
     * @param $base64_image_content [要保存的Base64]
     * @param $path [要保存的路径]
     * @return bool|string
     */
    public function base64_image_content($base64_image_content,$path){
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            $new_file = $path."/".date('Ymd',time())."/";
            $basePutUrl = C('UPLOAD_IMG_BASE64_URL').$new_file;

            if(!file_exists($basePutUrl)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($basePutUrl, 0700);
            }
            $ping_url = genRandomString(8).time().".{$type}";
            $ftp_image_upload_url = $new_file.$ping_url;
            $local_file_url = $basePutUrl.$ping_url;

            if (file_put_contents($local_file_url, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            //TODO 个人业务的FTP 账号图片上传
            ftp_upload(C('REMOTE_ROOT').$ftp_image_upload_url,$local_file_url);
            return $ftp_image_upload_url;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 临时测试接口
     * api/Mobilereceive/postMobilereceive
     */
    public function postMobilereceive()
    {
        var_dump(request.getInputStream());
    }
}