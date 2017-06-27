<?php
/**
 * @Author: Administrator
 * @Date:   2016-07-23 11:11:00
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-10 08:16:01
 */

return array(
	//验证码位数
	'CODE_LEN' =>4,
	//设置默认时区
	'DEFAULT_TIME_ZONE'=>'PRC',
	//自动开始session
	'SESSION_AUTO_START'=>true,

	'VAR_CONTROLLER'=>'c',//设置url控制器变量
	'VAR_ACTION'=>'a',//设置url方法变量
	//是否开启日志记录功能
	'SAVE_LOG'=>1,
	//错误跳转的地址
	'ERROR_URL' => '',
	//错误跳转的提示
	'ERROR_MSG' => '网站出错了，请稍后再试。。。',
	'AUTO_LOAD_FILE' => '',

	//数据库配置
	'DB_DATABASE' => '',
	'DB_HOST'     => '127.0.0.1',
	'DB_PORT'	  => 3306,
	'DB_USER'     => 'root',
	'DB_PASSWORD' => '',
	'DB_CHARSET'  => 'utf8',
	'DB_PREFIX'   => '',

	'LOG_LEVER'   => 'EMERG,ALERT,CRIT,ERR',
	'LOG_TIME_FORMAT' => 'c',
	'LOG_FILE_SIZE' => 2097152,

	// smarty缓存
	'CACHE_ON'     =>  1,
	'CACHE_TIME'  =>  3,
	'LEFT_DELIMITER' => '{mp',
	'RIGHT_DELIMITER'=> '}',
	'SMARTY_ON'      =>1,
	);