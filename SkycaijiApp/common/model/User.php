<?php
/*
 |--------------------------------------------------------------------------
 | SkyCaiji (蓝天采集器)
 |--------------------------------------------------------------------------
 | Copyright (c) 2018 https://www.skycaiji.com All rights reserved.
 |--------------------------------------------------------------------------
 | 使用协议  https://www.skycaiji.com/licenses
 |--------------------------------------------------------------------------
 */

namespace skycaiji\common\model;

class User extends BaseModel{
	/*获取随机盐*/
	public static function rand_salt($len=20){
		$salt="QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
		$salt=str_shuffle($salt);
		if($len>=strlen($salt)){
			return $salt;
		}else{
			return substr($salt,mt_rand(0,strlen($salt)-$len-1),$len);
		}
	}
	/*密码加密*/
	public static function pwd_encrypt($pwd,$salt=''){
		$pwd=sha1($pwd);
		if(!empty($salt)){
			$pwd.=$salt;
		}
		return md5($pwd);
	}
	/*用户名是否正确*/
	public static function right_username($username,$name='username'){
	    $return=array('name'=>$name);
	    if(!preg_match('/^.{3,15}$/i', $username)){
	        $return['msg']=lang('user_error_username');
	    }else{
	        $return['success']=true;
	    }
	    return $return;
	}
	/**
	 * 密码格式是否正确
	 * @param string $pwd
	 * @param string $name
	 * @return Ambigous <multitype:, multitype:string , multitype:boolean string >
	 */
	public static function right_pwd($pwd,$name='password'){
	    $return=array('name'=>$name);
	    if(!preg_match('/^[a-zA-Z0-9\`\~\!\@\#\$\%\^\*\(\)\-\_\+\=\|\{\}\[\]\:\;\,\.\?\&\'\"\<\>]{6,30}$/i', $pwd)){
	        $return['msg']=lang('user_error_password');
	    }else{
	        $return['success']=true;
	    }
	    return $return;
	}
	/**
	 * 验证密码是否一致
	 * @param string $pwd
	 * @param string $repwd
	 * @param string $name
	 * @return multitype:string |multitype:boolean string
	 */
	public static function right_repwd($pwd,$repwd,$name='repassword'){
	    if($pwd!=$repwd){
	        return array('msg'=>lang('user_error_repassword'),'name'=>$name);
	    }else{
	        return array('success'=>true,'name'=>$name);
	    }
	}
	/*邮箱是否正确*/
	public static function right_email($email,$name='email'){
	    $return=array('name'=>$name,'field'=>'email');
	    if(!preg_match('/^[^\s]+\@([\w\-]+\.){1,}\w+$/i', $email)){
	        $return['msg']=lang('user_error_email');
	    }else{
	        $return['success']=true;
	    }
	    return $return;
	}
}

?>