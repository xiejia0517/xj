<?php
namespace app\api\controller;
use app\admin\model\t_permission as TpermissionModel;

use think\Controller;

class Tpermission extends Controller
{
    public function lst()
    {
        // echo '1';die;
        $tp_model = new TpermissionModel();
        // $tpres = TpermissionModel::with("t_permission_group")->find(1);
        $tpres = $tp_model -> find(1);
        dump($tpres);
    }
}

