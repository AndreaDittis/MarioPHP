<?php  /**
 * @Author: Administrator
 * @Date:   2016-07-23 09:17:31
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-07 23:43:21
 */

function E($error,$level='ERROR',$type=3,$dest=NULL){
	if (is_array($error)) {
		log::write($error['message'],$lever,$type,$dest);
	}else{
		log::write($error,$level,$type,$dest);
	}

	$e = array();
	if (DEBUG) {
		if(!is_array($error)){
			$trace=debug_backtrace();
			$e['message'] = $error;
			$e['file'] = $trace[0]['file'];
			$e['line'] = $trace[0]['line'];
			$e['function'] = isset($trace[0]['function']) ? $trace[0]['function'] : '';
			$e['class'] = isset($trace[0]['class']) ? $trace[0]['class'] : '';
			ob_start();
			debug_print_backtrace();
			$e['trace']=htmlspecialchars(ob_get_clean());
			// p($trace);
			// var_dump(debug_backtrace());
		}else{
			$e = $error;
		}		
	}else{
		if($url = C('ERROR_URL')){
			go($url);
		}else{
			$e['message'] = C('ERROR_MSG');
		}

	}

	include DATA_PATH . '/Tpl/E.html';
	die;
}
/**
 * [p 打印函数]
 * @param  [type] $arr [description]
 * @return [type]      [description]
 */
 function p($arr){
 	if(is_bool($arr)){
 		var_dump($arr);
 	}else if (is_null($arr)) {
 		var_dump($arr);		
 	}else{
   		echo '<pre>'. print_r($arr,true).'</pre>';
  	}
 }
/**
 * [go 跳转函数]
 * @param  [type]  $url  [description]
 * @param  integer $time [description]
 * @param  string  $msg  [description]
 * @return [type]        [description]
 */
function go($url,$time=0,$msg=''){
	if(!headers_sent()){
		$time = 0 ? header('location:'.$url):header("refresh:{$time};url={$url}");
		die($msg);
	}else{
		echo "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if($time) die($msg);
	}
}
//1.加载配置项
//C($sysConfig) C($userConfig);
//2.读取配置
//C('CODE_LEN');
//3.临时动态改变配置项,使当前文件配置的改变
//C('CODE_LEN',20);
//4.C()返回所有的配置项
 function C($var = NULL,$value = NULL){
 	static $config=array();
 	//先加载系统配置项，后加载用户配置项
 	if(is_array($var)){
 		$config =array_merge($config , array_change_key_case($var,CASE_UPPER));
 		return;
 	}
 	//
 	if(is_string($var)){
 		$var=strtoupper($var);
 		//传递俩个参数，临时设置配置项
 		if(!is_null($value)){
 			$config[$var]=$value;
 			return;
 		}
 		//传递一个参数，返回一个值
 		return isset($config[$var]) ? $config[$var] : NULL;
 	}
 	//不传递参数，返回全部配置项
 	if(is_null($var) && is_null($value)){
 		return $config;
 	}
 }

 function print_const(){
 	$const = get_defined_constants(true);
 	p($const['user']);
 }

 function M($table){
 	$model = new Model($table);
 	return $model;
 }

 function K($model){
 	$model.='Model';
 	return new $model;
 }
 ?>