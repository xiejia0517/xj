<?php
namespace app\api\controller;

use \think\Controller;
use \think\Db;
use \think\Session;
use \think\Cookie;
use \think\Request;

/*
 * 会员基类
 */
class MemberBase extends Controller
{
    public $MemberInfo;
    public $MemberSession;

    public function _initialize()
    {
        parent::_initialize();
        $this->MemberInfo = null;
        $this->MemberSession = Session::get();
        if(!isset($this->MemberSession["is_login"])) $this->MemberSession["is_login"] = "0";
        $this->checkLogin();
        $this->getMemberInfo();
    }


    /**
     * 获取会员详细资料
     *
     */
    protected function getMemberInfo()
    {
        $this->MemberInfo = db('member')->where("member_id", $this->MemberSession["member_id"])->find();
    }


    /**
     * 验证会员是否登录
     *
     */
    protected function checkLogin()
    {
        if ($this->MemberSession["is_login"] !== '1') {
            if(Request::instance()->isAjax() && Request::instance()->get('_ajax')!="0"){
                api_return([], 2, lang('no_login'));
            }
            // 临时采用客户端跳转，用以解决登录成功后返回当前URL
            echo("<script>document.location.href='/wap/member/login.html';</script>");
            exit;
            /*
            $ref_url = request_uri();
            @header("location: /wap/member/login.html?ref_url=" . urlencode($ref_url));
            exit;
            */
        }
    }
}
