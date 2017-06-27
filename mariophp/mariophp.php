<?php 
	final class mariophp{
		public static function run(){
			self::_set_const();
			defined('DEBUG') || define('DEBUG', true);
			if (DEBUG) {	# code...
				self::_create_dir();
				self::_import_file();
			}else{
				error_reporting(0);
				require APP_TMPL_PATH .'/~boot.php';
			}
			Application::run();//相当于 think::start();
		}

		private static function _set_const(){
			$path=str_replace('\\', '/', __FILE__);
			// 框架根目录
			define('MARIOPHP_PATH',dirname($path));
			//DEBUG
			defined('APP_DEBUG')    or define('APP_DEBUG',      false);
			//echo MARIOPHP_PATH;//D:/xampp/htdocs/mariophp
			define('CONFIG_PATH',MARIOPHP_PATH.'/Config');
			define('DATA_PATH', MARIOPHP_PATH.'/Data');
			define('LIB_PATH',MARIOPHP_PATH.'/Lib');
			define('CORE_PATH', LIB_PATH.'/Core');
	 		define('FUNCTION_PATH', LIB_PATH.'/Function');
	 		
	 		// 项目根目录
	 		define('ROOT_PATH',dirname(MARIOPHP_PATH));
	 		// echo ROOT_PATH;
	 		define('APP_PATH', ROOT_PATH.'/'.APP_NAME);
	 		// echo APP_PATH;
	 		define('APP_CONFIG_PATH', APP_PATH.'/Config');
	 		define('APP_CONTROLLER_PATH', APP_PATH.'/Controller');
	 		define('APP_TPL_PATH',APP_PATH. '/Tpl');
	 		define('APP_PUBLIC_PATH', APP_TPL_PATH.'/Public');

	 		//定义临时目录变量
	 		define('APP_TMPL_PATH',APP_PATH.'/Temp' );
	 		define('APP_LOG_PATH', APP_TMPL_PATH.'/Log');
	 		//创建compile 和 cache 目录
	 		define('APP_COMPILE_PATH', APP_TMPL_PATH . '/Complie');
	 		define('APP_CACHE_PATH', APP_TMPL_PATH . '/Cache'); 

	 		//判断是否post提交
	 		define('IS_POST', ($_SERVER['REQUEST_METHOD'] == 'POST') ? true : false);
	 		//判断是否ajax提交
	 		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
	 			define('IS_AJAX', true);
	 		}else{
	 			define('IS_AJAX', false);
	 		}
	 		
	 		//创建公共文件夹
	 		define('COMMON_PATH', ROOT_PATH . '/common' );
	 		define('COMMON_CONFIG_PATH', COMMON_PATH . '/Config');
	 		define('COMMON_LIB_PATH', COMMON_PATH .'/Lib');
	 		define('COMMON_MODEL_PATH', COMMON_PATH. '/Model');

	 		//创建扩展文件夹
	 		define('EXTENDS_PATH', MARIOPHP_PATH.'/Extends');
	 		define('EXTENDS_ORG_PATH', EXTENDS_PATH.'/Org');
	 		define('EXTENDS_TOOLS_PATH', EXTENDS_PATH.'/Tools');

	 		//定义框架版本
	 		define('MARIOPHP_VERSION', '1.0');
		}
		/**
		 * [_create_dir 创建应用框架目录]
		 * @return [type] [description]
		 */
		private static function _create_dir(){
			$arr=array(
				COMMON_PATH,
				COMMON_LIB_PATH,
				COMMON_MODEL_PATH,
				COMMON_CONFIG_PATH,
				EXTENDS_PATH,
				EXTENDS_TOOLS_PATH,
				EXTENDS_ORG_PATH,
				APP_PATH,
				APP_CONFIG_PATH,
				APP_CONTROLLER_PATH,
				APP_TPL_PATH,
				APP_PUBLIC_PATH,
				APP_TMPL_PATH,//创建临时目录
				APP_COMPILE_PATH,
				APP_CACHE_PATH,
				APP_LOG_PATH//创建日志
				);
			foreach($arr as $v){
				is_dir($v) || mkdir($v,0777,true);//目录存在不走后面，创建目录给最高权限，true表示递归创建
			}

			//如果用户自定义了成功/失败模板则使用用户自定义模板
			is_file(APP_TPL_PATH . '/success.html') || copy(DATA_PATH.'/Tpl/success.html',APP_TPL_PATH.'/success.html');
			is_file(APP_TPL_PATH . '/error.html') || copy(DATA_PATH.'/Tpl/error.html',APP_TPL_PATH.'/error.html');
		}

		private static function _import_file(){
			$fileArr =array(
				CORE_PATH.'/Log.class.php',
				 FUNCTION_PATH.'/function.php',
				 EXTENDS_ORG_PATH .'/Smarty/Smarty.class.php',
				 CORE_PATH . '/SmartyView.class.php',
				 // 注意引入的顺序，一定是先引用controller再applicatin
				 CORE_PATH.'/Controller.class.php',
				 CORE_PATH.'/Application.class.php'
				);

			$str = '';
			foreach($fileArr as $v){
				 $str .=trim(substr(file_get_contents($v),5,-2));
				 require_once $v;
			}
			$str.="<?php\r\n".$str; 
			file_put_contents(APP_TMPL_PATH.'/~boot.php',$str) || die('access not allow');
		}
	}

	mariophp::run(); 
 ?>