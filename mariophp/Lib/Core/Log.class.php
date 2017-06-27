<?php
/**
 * @Author: Administrator
 * @Date:   2016-07-24 22:49:18
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-09 22:59:31
 */

class Log{

	// 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';  // SQL：SQL语句 

	static protected $log = array();

	/**
	 * [write 日志直接写入]
	 * @param  [type]  $msg   [日志信息]
	 * @param  string  $level [日志级别]
	 * @param  integer $type  [日记记录方式]
	 * @param  [type]  $dest  [写入目标]
	 * @return void
	 */
	public static function write($msg,$level='ERROR',$type=3,$dest=NULL){
		// 是否开启日志记录功能
		if(!C('SAVE_LOG')) return;
		if(is_null($dest)){
			$dest = APP_LOG_PATH .'/'. date('Y-m-d') .'.log';
		}

		//检测日志文件大小，超过配置大小则备份日志重新生成
		if(is_file($dest) && floor(C('LOG_FILE_SIZE')) <= filesize($dest)){
			rename($dest, dirname($dest) . '/' . time() .'-' . basename($dest));
			// basename返回路径中的文件名部分
		}

		// error_log() 日志类型 $type
		// 0 message 发送到 PHP 的系统日志，使用 操作系统的日志机制或者一个文件，取决于 error_log 指令设置了什么。 这是个默认的选项。  
		// 1 message 发送到参数 destination 设置的邮件地址。 第四个参数 extra_headers 只有在这个类型里才会被用到。  
		// 2 不再是一个选项。  
		// 3 message 被发送到位置为 destination 的文件里。 字符 message 不会默认被当做新的一行。  
		// 4 message 直接发送到 SAPI 的日志处理程序中。  
		if(is_dir(APP_LOG_PATH)) error_log("[TIME]".date('Y-m-d H:i:s')."{$level}:{$msg}\r\n",$type,$dest);


	}
	/**
	 * [record 记录日志 并且会过滤未经设置的级别]
	 * @param  [type] $msg    [日志信息]
	 * @param  string $level  [日志等级]
	 * @param  string $record [是否强制记录]
	 * @return void
	 */
	static function record($msg,$level='ERROR',$record='false'){
		if($record || strpos(C('LOG_LEVER'),$level) !== false)
			$log[] =  "{$level}: {$msg}\r\n";

	}
}