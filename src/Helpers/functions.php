<?php

if(!function_exists('getRandChar')){
	/**
	 * Notes: 生成随机长度字符串
	 * @param $length
	 * @return string|null
	 */
	function getRandChar($length)
	{
	    $str = null;
	    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	    $max = strlen($strPol) - 1;
	    for ($i = 0;
	         $i < $length;
	         $i++) {
	        $str .= $strPol[rand(0, $max)];
	    }
	    return $str;
	}
}

if(!function_exists('generatePassword')){
	/**
	 * Notes: 生成密码
	 * @param $plaintext

	 * @return string
	 */
	function generatePassword($plaintext, $salt)
	{
	    $salt = md5('y' . $salt . 'x');
	    $salt .= '2021';
	    return md5($plaintext . $salt);
	}
}

if(!function_exists('create_user_sn')){
	/**
	 * 生成会员码
	 * @return 会员码
	 */
	function create_user_sn($prefix = '', $length = 8)
	{
	    $rand_str = '';
	    for ($i = 0; $i < $length; $i++) {
	        $rand_str .= mt_rand(0, 9);
	    }
	    $sn = $prefix . $rand_str;
	    return $sn;
	}
}


