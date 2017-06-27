<?php
/**
 * @Author: Administrator
 * @Date:   2016-07-23 10:47:19
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-10 08:16:50
 */
	final class Application{
		public static function run(){
			self::_init();
			set_error_handler(array(__CLASS__, 'error'));
			register_shutdown_function(array(__CLASS__,'fatal_error'));
			self::_user_import();
			self::_set_url();//设置外部路径
			//如果這个类（new的类）没有载入则自动载入
			spl_autoload_register(array(__CLASS__,'_autoload')) ;
			//p(__CLASS__); 
			self::_create_demo();//创建一个demo(XXXController.class.php)
			self::_app_run();//实例化应用控制器
		}
		public static function fatal_error(){
			if($e = error_get_last()){
				self::error($e['type'],$e['message'],$e['file'],$e['line']);
			}
		}
		/**
		 * [error 使用框架的notice错误模板]
		 * @param  [type] $errno [错误类型]
		 * @param  [type] $error [错误原因]
		 * @param  [type] $file  [错误文件]
		 * @param  [type] $line  [错误行数]
		 * @return [type]        [null]
		 */
		public static function error($errno,$error,$file,$line){
			switch ($errno) {
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
					// if(C('SAVE_LOG')) Log::write("[$errno]" . $errorStr);
					E($error . $file ."第{$line}行");
					break;
				case E_STRICT:
				case E_USER_WARNING:
				case E_USER_NOTICE:
				default:
					if(DEBUG){
						include DATA_PATH .'/Tpl/notice.html';
					}
					break;
			}
		} 
		/**
		 * [_app_run 实例化应用控制器]
		 * @return [type] [description]
		 */
		private static function _app_run(){
			$c = isset($_GET[C('VAR_CONTROLLER')]) ? $_GET[C('VAR_CONTROLLER')] : 'Index';
			$a = isset($_GET[C('VAR_ACTION')]) ? $_GET[C('VAR_ACTION')]:'index';

			define('CONTROLLER', $c);
			define('ACTION', $a);

			$c.='Controller';
			if(class_exists($c)){
				$obj = new $c();
				if(!method_exists($obj, $a)){
					if(method_exists($obj, '__empty')){
						$obj->__empty();
					}else{
						E($c . '控制器' . $a .'方法不存在');
					}
				}else{
					$obj->$a();
				}
				
			}else{
				$obj = new EmptyController();
				$obj->index();
			}
			
		}
		/**
		 * [_create_demo 创建一个demo(XXXController.class.php)]
		 * @return [type] [description]
		 */
		private static function _create_demo(){
			$path=APP_CONTROLLER_PATH.'/IndexController.class.php';

			$str=<<<str
<?php
class IndexController extends Controller{
	public function index(){
		header('Content-type:text/html;char-set:utf-8');
		echo '<br/><br/><h2>欢迎使用MarioPHP框架<p style="font-size:100px;">:)</p></h2>';
	}
}
?>
str;
			is_file($path) || file_put_contents($path, $str);
		}

		/**
		 * [_autoload 自动载入类功能]
		 * @param  [type] $className [类名]
		 * @return [type]            [description]
		 */
		private static function _autoload($className){
			switch (true) {
				case strlen($className) >10 && substr($className, -10) == 'Controller':
					$path = APP_CONTROLLER_PATH . '/' . $className . '.class.php';
					if(!is_file($path))
						 $path = APP_CONTROLLER_PATH .'/EmptyController.class.php';
						 if(is_file($path)){
						 	include $path;
						 	return;
						 }else{
						 	E($path .'控制器未找到');
						 } 
						
					include $path;
					break;

				case strlen($className) > 5 && substr($className, -5) == 'Model':
					$path = COMMON_MODEL_PATH . '/' . $className . '.class.php';
					include $path;
					break;
				
				default:
					$path = EXTENDS_TOOLS_PATH.'/'.$className.'.class.php';
					if (!is_file($path)) E($path .'类没找到');
					include $path;
					break;
			}
			
		}
		/**
		 * [_set_url 设置外部路径]
		 */
		private static function _set_url(){
			// p($_SERVER);
			$path = 'http//:'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'];
			$path = str_replace('\\', '/' , $path);
			define('__APP__', $path);
			define('__ROOT__', dirname($path));
			// p(__APP__);
			// p(__ROOT__);
			define('__TPL__', __ROOT__.'/'.APP_NAME.'/Tpl');
			define('__PUBLIC__', __TPL__.'/Public');
		}
		/**
		 * [_init 初始化框架]
		 * @return [type] [description]
		 */
		private static function _init(){
			//加载配置项，用户优先级更高
			//加载系统框架配置项
			C(include CONFIG_PATH.'/config.php');

			$commonPath=COMMON_CONFIG_PATH.'/config.php';

			$commonConfig= <<<str
<?php
	return array(
		//配置项=>配置值
	);
?>
str;
			is_file($commonPath) || file_put_contents($commonPath, $commonConfig);
			C(include $commonPath);
			//加载用户配置项
			$userPath=APP_CONFIG_PATH.'/config.php';

			$userConfig= <<<str
<?php
	return array(
		//配置项=>配置值
	);
?>
str;
			is_file($userPath) || file_put_contents($userPath, $userConfig);
			C(include $userPath);

			//设置默认时区
			date_default_timezone_set(C('DEFAULT_TIME_ZONE'));

			//自动开启session
			C('SESSION_AUTO_START') && session_start();
		}

		private static function _user_import(){
			$autoFile = C('AUTO_LOAD_FILE');
			if(is_array($autoFile) && !empty($autoFile)){
				foreach($autoFile as $af){
					require_once COMMON_LIB_PATH.'/'. $af . '.php';
					// require_once $autoPath;
				}
			}
		}
	}
 ?>