<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件



	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
	 * @return mixed
	 */
	function get_client_ip($type = 0, $adv=false) {
	    $type       =  $type ? 1 : 0;
	    static $ip  =   NULL;
	    if ($ip !== NULL) return $ip[$type];
	    if($adv){
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            $pos    =   array_search('unknown',$arr);
	            if(false !== $pos) unset($arr[$pos]);
	            $ip     =   trim($arr[0]);
	        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	            $ip     =   $_SERVER['REMOTE_ADDR'];
	        }
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u",ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
	}


	/**
	 * 产生随机字符串
	 *
	 * @param    int        $length  输出长度
	 * @param    string     $type  	 字符串类型 由0 1 2 3组合
	 * @param    string     $str     自定义字符串（可选的 ，默认空）
	 * @return   string     字符串
	 */
	function get_random($length=6, $type="012", $chars="") {
		if($chars==""){
			$data = [
				"0123456789",
				"abcdefghijklmnopqrstuvwxyz",
				"ABCDEFGHIJKLMNOPQRSTUVWXYZ",
				"-_+=()$@#%"
			];
	        $type_arr = array_unique(str_split(preg_replace("/[^0-3]/", "", $type)));
	        foreach ($type_arr as $k => $v) {
				$chars .= $data[intval($v)];
	        }
		}
		$result_ran = "";
	    $max = strlen($chars) - 1;
	    for($i = 0; $i < $length; $i++) {
	        $result_ran .= $chars[mt_rand(0, $max)];
	    }
	    return $result_ran;
	}

	/**
	 * 获取毫秒数
	 * @return int
	 */
	function get_millisecond() {
		list($t1, $t2) = explode(' ', microtime());
		return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
	}


	/**
	 * API返回接口
	 * return Json
	 */
	function api_return($data, $code=1, $msg="") {
		// echo 'api_return';die;
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Method: POST,GET');
		header('Content-Type: application/json');
		$code = is_numeric($code) ? $code : 1;
		if(!isset($data["ErrorCode"])) $data["ErrorCode"] = $code;
		if(!isset($data["ErrorMsg"])) $data["ErrorMsg"] = $msg;
		exit(json_encode($data));
	}


	/**
	 * 验证用户名合法性
	 * @param string $username 用户名
	 * @param int $type 类型（0=登录 else=注册）
	 * @param array $deny 屏蔽词
	 * return Array
	 */
    function username_verify($username, $type=0, $deny=null){
		if($username==''){
			return [
				"code" => false,
				"info" => "账号不能为空！"
			];
		}
		if(!preg_match("/^[a-zA-Z0-9]+$/", $username)){
			return [
				"code" => false,
				"info" => "账号只能是数字+字母！"
			];
		}
		if(strlen($username)<3 || strlen($username)>16){
			return [
				"code" => false,
				"info" => "账号长度应在3-16个字符！"
			];
		}
		// 注册时才需要检测项目
		if($type!=0){
			$deny = is_array($deny) ? $deny : config('USERNAME_DENY');
			foreach($deny as $v){
				if(strpos('='.$username, $v) > 0){
					return [
						"code" => false,
						"info" => "账号含有禁用词，请重新输入！"
					];
				}
			}
			if(db("User")->where("username='{$username}'")->count()>0){
				return [
					"code" => false,
					"info" => "账号已被使用，请重新输入！"
				];
			}
		}
		return [
			"code" => true,
			"info" => ""
		];
    }


	/**
	 * 验证密码合法性
	 * @param string $password 密码
	 * @param string $name 消息名（默认：密码）
	 * return Array
	 */
    function password_verify_lora($password="", $name="密码"){
    	if(!isset($password) || $password==""){
			return [
				"code" => 1,
				"info" => $name."不能为空"
			];
    	}
    	if(strlen($password)<6 || strlen($password)>16){
			return [
				"code" => 2,
				"info" => $name."长度应在6-16个字符"
			];
    	}
		return [
			"code" => 2,
			"info" => $name."长度应在6-16个字符"
		];
	}


	/**
	 * 系统加密方法
	 * @param string $data 要加密的字符串
	 * @param string $key  加密密钥
	 * @param int $expire  过期时间 单位 秒
	 * return string
	 */
	function think_encrypt($data, $key = '', $expire = 0) {
		$key  = md5(empty($key) ? config('DATA_AUTH_KEY') : $key);
		$data = base64_encode($data);
		$x    = 0;
		$len  = strlen($data);
		$l    = strlen($key);
		$char = '';
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) $x = 0;
			$char .= substr($key, $x, 1);
			$x++;
		}
		$str = sprintf('%010d', $expire ? $expire + time():0);
		for ($i = 0; $i < $len; $i++) {
			$str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
		}
		return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
	}
	
	/**
	 * 系统解密方法
	 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
	 * @param  string $key  加密密钥
	 * return string
	 */
	function think_decrypt($data, $key = ''){
		$key    = md5(empty($key) ? config('DATA_AUTH_KEY') : $key);
		$data   = str_replace(array('-','_'),array('+','/'),$data);
		$mod4   = strlen($data) % 4;
		if ($mod4) {
		   $data .= substr('====', $mod4);
		}
		$data   = base64_decode($data);
		$expire = substr($data,0,10);
		$data   = substr($data,10);
		if($expire > 0 && $expire < time()) {
			return '';
		}
		$x      = 0;
		$len    = strlen($data);
		$l      = strlen($key);
		$char   = $str = '';
		for ($i = 0; $i < $len; $i++) {
			if ($x == $l) $x = 0;
			$char .= substr($key, $x, 1);
			$x++;
		}
		for ($i = 0; $i < $len; $i++) {
			if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
				$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
			}else{
				$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
			}
		}
		return base64_decode($str);
	}

	/**
	 * 返回文件扩展名
	 *@param    string   $filename
	 */
	function get_file_ext($filename) {
		$arr = explode('.', $filename);
		if(count($arr)>1)
		{
			return $arr[count($arr)-1];
		}else{
			return "";
		}
	}

	/**
	 * Email 打*隐私
	 *@param    string   $email
	 */
	function email_privacy($email) {
		$array = explode('@', $email);
		if(count($array)!=2) return false;
		$len = strlen($array[0]);
		if($len<1) return false;
		if($len==1){
			$name = "*";
		}else{
			$len_show = intval($len/2);
			$name = substr($array[0], 0, $len_show);
			$name .= str_repeat("*", $len-$len_show);
		}
		return $name."@".$array[1];
	}

	/**
	 * 文件大小转换
	 *@param    int   $filesize
	 */
	function sizecount($filesize) {
		if($filesize>=1073741824){ // 1024*1024*1024
            $size = intval(($filesize/1073741824)*100)/100;
            return "{$size} GB";
        }
        if($filesize>=1048576){
            $size = intval(($filesize/1048576)*100)/100;
            return "{$size} MB";
        }
        if($filesize>=1024){
            $size = intval(($filesize/1024)*100)/100;
            return "{$size} KB";
        }
        return "{$filesize} B";
	}

// 2018-11-07 ======================================================================================

	/**
	 * @param string $url get请求地址
	 * @param int $httpCode 返回状态码
	 * @return mixed
	 */
	function curl_get($url, &$httpCode = 0)
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	    //不做证书校验,部署在linux环境下请改为true
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    $file_contents = curl_exec($ch);
	    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    return $file_contents;
	}


	/**
	 * @param string $url post请求地址
	 * @param array $params
	 * @return mixed
	 */
	function curl_post($url, array $params = array())
	{
	    $data_string = json_encode($params);
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	    curl_setopt(
	        $ch, CURLOPT_HTTPHEADER,
	        array(
	            'Content-Type: application/json'
	        )
	    );
	    $data = curl_exec($ch);
	    curl_close($ch);
	    return ($data);
	}

/**
 * php低版本array_column 合并
 * @param array $array
 * @param $column_key
 * @param null $index_key
 * @return array
 */
    function _array_column(array $array, $column_key, $index_key=null){
        $result = [];
        foreach($array as $arr) {
            if(!is_array($arr)) continue;

            if(is_null($column_key)){
                $value = $arr;
            }else{
                $value = $arr[$column_key];
            }

            if(!is_null($index_key)){
                $key = $arr[$index_key];
                $result[$key] = $value;
            }else{
                $result[] = $value;
            }
        }
        return $result;
    }
