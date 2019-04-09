<?php
namespace app\index\controller;

use think\Controller;


class Index extends Controller
{
    public function index()
    {
        // return view('ajax');
        // return view('lunbo');
        return view('_index');
        // return view();
    }
}
