<?php
namespace app\api\controller;

use \think\Controller;

class Doc extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}

