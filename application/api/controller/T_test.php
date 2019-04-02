<?php
namespace app\api\controller;
use \think\Controller;
use app\api\model\t_test as TestModel;
class T_test extends Controller
{
    public function test()
    {
        $tm = new TestModel();
        $tmres = $tm -> where('id',2)->find();
        dump($tmres);
    }
}