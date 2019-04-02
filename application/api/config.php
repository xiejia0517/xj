<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
	"WX_LOGIN" => [
		"URL" => "https://api.weixin.qq.com/sns/jscode2session",
		"DATA" => [
			"appid" 		=> "wxa675c6749bec7a2a",
			"secret" 		=> "0e0d1878571fa9792b7ae3943a7770fb",
			"js_code" 		=> "",
			"grant_type" 	=> "authorization_code"

		]
	],
    "REMOTE_SERVER" => "http://47.106.12.142:8808/",
    "PAGE_NUMBER" => 20,

    "FAKE_DATA" => false,
    "FAKE_DATA_COEFFICIENT" => 2,
    "TASK_MASK_LEVEL" => 5 // 隐藏任务名称时，首尾各保留的字符数

];
