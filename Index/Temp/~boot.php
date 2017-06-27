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

	}/**
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
 }/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com
 *
 * @link      http://www.smarty.net/
 * @copyright 2016 New Digital Group, Inc.
 * @copyright 2016 Uwe Tews
 * @author    Monte Ohrt <monte at ohrt dot com>
 * @author    Uwe Tews
 * @author    Rodney Rehm
 * @package   Smarty
 * @version   3.1.30
 */

/**
 * define shorthand directory separator constant
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * Sets SMARTY_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DS);
}

/**
 * set SMARTY_SYSPLUGINS_DIR to absolute path to Smarty internal plugins.
 * Sets SMARTY_SYSPLUGINS_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_SYSPLUGINS_DIR')) {
    define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins' . DS);
}
if (!defined('SMARTY_PLUGINS_DIR')) {
    define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins' . DS);
}
if (!defined('SMARTY_MBSTRING')) {
    define('SMARTY_MBSTRING', function_exists('mb_get_info'));
}
if (!defined('SMARTY_RESOURCE_CHAR_SET')) {
    // UTF-8 can only be done properly when mbstring is available!
    /**
     * @deprecated in favor of Smarty::$_CHARSET
     */
    define('SMARTY_RESOURCE_CHAR_SET', SMARTY_MBSTRING ? 'UTF-8' : 'ISO-8859-1');
}
if (!defined('SMARTY_RESOURCE_DATE_FORMAT')) {
    /**
     * @deprecated in favor of Smarty::$_DATE_FORMAT
     */
    define('SMARTY_RESOURCE_DATE_FORMAT', '%b %e, %Y');
}

/**
 * Try loading the Smarty_Internal_Data class
 * If we fail we must load Smarty's autoloader.
 * Otherwise we may have a global autoloader like Composer
 */
if (!class_exists('Smarty_Autoloader', false)) {
    if (!class_exists('Smarty_Internal_Data', true)) {
        require_once dirname(__FILE__) . '/Autoloader.php';
        Smarty_Autoloader::registerBC();
    }
}

/**
 * Load always needed external class files
 */
if (!class_exists('Smarty_Internal_Data', false)) {
    require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_data.php';
}
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_extension_handler.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_templatebase.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_template.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_resource.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_variable.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_template_source.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_template_resource_base.php';

/**
 * This is the main Smarty class
 *
 * @package Smarty
 *
 * The following methods will be dynamically loaded by the extension handler when they are called.
 * They are located in a corresponding Smarty_Internal_Method_xxxx class
 *
 * @method int clearAllCache(int $exp_time = null, string $type = null)
 * @method int clearCache(string $template_name, string $cache_id = null, string $compile_id = null, int $exp_time = null, string $type = null)
 * @method int compileAllTemplates(string $extension = '.tpl', bool $force_compile = false, int $time_limit = 0, int $max_errors = null)
 * @method int compileAllConfig(string $extension = '.conf', bool $force_compile = false, int $time_limit = 0, int $max_errors = null)
 * @method int clearCompiledTemplate($resource_name = null, $compile_id = null, $exp_time = null)
 */
class Smarty extends Smarty_Internal_TemplateBase
{
    /**#@+
     * constant definitions
     */

    /**
     * smarty version
     */
    const SMARTY_VERSION = '3.1.30';

    /**
     * define variable scopes
     */
    const SCOPE_LOCAL = 1;

    const SCOPE_PARENT = 2;

    const SCOPE_TPL_ROOT = 4;

    const SCOPE_ROOT = 8;

    const SCOPE_SMARTY = 16;

    const SCOPE_GLOBAL = 32;

    /**
     * define caching modes
     */
    const CACHING_OFF = 0;

    const CACHING_LIFETIME_CURRENT = 1;

    const CACHING_LIFETIME_SAVED = 2;

    /**
     * define constant for clearing cache files be saved expiration dates
     */
    const CLEAR_EXPIRED = - 1;

    /**
     * define compile check modes
     */
    const COMPILECHECK_OFF = 0;

    const COMPILECHECK_ON = 1;

    const COMPILECHECK_CACHEMISS = 2;

    /**
     * define debug modes
     */
    const DEBUG_OFF = 0;

    const DEBUG_ON = 1;

    const DEBUG_INDIVIDUAL = 2;

    /**
     * modes for handling of "<?php ... ?>" tags in templates.
     */
    const PHP_PASSTHRU = 0; //-> print tags as plain text

    const PHP_QUOTE = 1; //-> escape tags as entities

    const PHP_REMOVE = 2; //-> escape tags as entities

    const PHP_ALLOW = 3; //-> escape tags as entities

    /**
     * filter types
     */
    const FILTER_POST = 'post';

    const FILTER_PRE = 'pre';

    const FILTER_OUTPUT = 'output';

    const FILTER_VARIABLE = 'variable';

    /**
     * plugin types
     */
    const PLUGIN_FUNCTION = 'function';

    const PLUGIN_BLOCK = 'block';

    const PLUGIN_COMPILER = 'compiler';

    const PLUGIN_MODIFIER = 'modifier';

    const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';

    /**
     * Resource caching modes
     * (not used since 3.1.30)
     */
    const RESOURCE_CACHE_OFF = 0;

    const RESOURCE_CACHE_AUTOMATIC = 1; // cache template objects by rules

    const RESOURCE_CACHE_TEMPLATE = 2; // cache all template objects

    const RESOURCE_CACHE_ON = 4;    // cache source and compiled resources

    /**#@-*/

    /**
     * assigned global tpl vars
     */
    public static $global_tpl_vars = array();

    /**
     * error handler returned by set_error_handler() in Smarty::muteExpectedErrors()
     */
    public static $_previous_error_handler = null;

    /**
     * contains directories outside of SMARTY_DIR that are to be muted by muteExpectedErrors()
     */
    public static $_muted_directories = array();

    /**
     * Flag denoting if Multibyte String functions are available
     */
    public static $_MBSTRING = SMARTY_MBSTRING;

    /**
     * The character set to adhere to (e.g. "UTF-8")
     */
    public static $_CHARSET = SMARTY_RESOURCE_CHAR_SET;

    /**
     * The date format to be used internally
     * (accepts date() and strftime())
     */
    public static $_DATE_FORMAT = SMARTY_RESOURCE_DATE_FORMAT;

    /**
     * Flag denoting if PCRE should run in UTF-8 mode
     */
    public static $_UTF8_MODIFIER = 'u';

    /**
     * Flag denoting if operating system is windows
     */
    public static $_IS_WINDOWS = false;

    /**#@+
     * variables
     */

    /**
     * auto literal on delimiters with whitespace
     *
     * @var boolean
     */
    public $auto_literal = true;

    /**
     * display error on not assigned variables
     *
     * @var boolean
     */
    public $error_unassigned = false;

    /**
     * look up relative file path in include_path
     *
     * @var boolean
     */
    public $use_include_path = false;

    /**
     * template directory
     *
     * @var array
     */
    protected $template_dir = array('./templates/');

    /**
     * flags for normalized template directory entries
     *
     * @var array
     */
    protected $_processedTemplateDir = array();

    /**
     * flag if template_dir is normalized
     *
     * @var bool
     */
    public $_templateDirNormalized = false;

    /**
     * joined template directory string used in cache keys
     *
     * @var string
     */
    public $_joined_template_dir = null;

    /**
     * config directory
     *
     * @var array
     */
    protected $config_dir = array('./configs/');

    /**
     * flags for normalized template directory entries
     *
     * @var array
     */
    protected $_processedConfigDir = array();

    /**
     * flag if config_dir is normalized
     *
     * @var bool
     */
    public $_configDirNormalized = false;

    /**
     * joined config directory string used in cache keys
     *
     * @var string
     */
    public $_joined_config_dir = null;

    /**
     * default template handler
     *
     * @var callable
     */
    public $default_template_handler_func = null;

    /**
     * default config handler
     *
     * @var callable
     */
    public $default_config_handler_func = null;

    /**
     * default plugin handler
     *
     * @var callable
     */
    public $default_plugin_handler_func = null;

    /**
     * compile directory
     *
     * @var string
     */
    protected $compile_dir = './templates_c/';

    /**
     * flag if template_dir is normalized
     *
     * @var bool
     */
    public $_compileDirNormalized = false;

    /**
     * plugins directory
     *
     * @var array
     */
    protected $plugins_dir = array();

    /**
     * flag if plugins_dir is normalized
     *
     * @var bool
     */
    public $_pluginsDirNormalized = false;

    /**
     * cache directory
     *
     * @var string
     */
    protected $cache_dir = './cache/';

    /**
     * flag if template_dir is normalized
     *
     * @var bool
     */
    public $_cacheDirNormalized = false;

    /**
     * force template compiling?
     *
     * @var boolean
     */
    public $force_compile = false;

    /**
     * check template for modifications?
     *
     * @var boolean
     */
    public $compile_check = true;

    /**
     * use sub dirs for compiled/cached files?
     *
     * @var boolean
     */
    public $use_sub_dirs = false;

    /**
     * allow ambiguous resources (that are made unique by the resource handler)
     *
     * @var boolean
     */
    public $allow_ambiguous_resources = false;

    /**
     * merge compiled includes
     *
     * @var boolean
     */
    public $merge_compiled_includes = false;

    /**
     * force cache file creation
     *
     * @var boolean
     */
    public $force_cache = false;

    /**
     * template left-delimiter
     *
     * @var string
     */
    public $left_delimiter = "{";

    /**
     * template right-delimiter
     *
     * @var string
     */
    public $right_delimiter = "}";

    /**#@+
     * security
     */
    /**
     * class name
     * This should be instance of Smarty_Security.
     *
     * @var string
     * @see Smarty_Security
     */
    public $security_class = 'Smarty_Security';

    /**
     * implementation of security class
     *
     * @var Smarty_Security
     */
    public $security_policy = null;

    /**
     * controls handling of PHP-blocks
     *
     * @var integer
     */
    public $php_handling = self::PHP_PASSTHRU;

    /**
     * controls if the php template file resource is allowed
     *
     * @var bool
     */
    public $allow_php_templates = false;

    /**#@-*/
    /**
     * debug mode
     * Setting this to true enables the debug-console.
     *
     * @var boolean
     */
    public $debugging = false;

    /**
     * This determines if debugging is enable-able from the browser.
     * <ul>
     *  <li>NONE => no debugging control allowed</li>
     *  <li>URL => enable debugging when SMARTY_DEBUG is found in the URL.</li>
     * </ul>
     *
     * @var string
     */
    public $debugging_ctrl = 'NONE';

    /**
     * Name of debugging URL-param.
     * Only used when $debugging_ctrl is set to 'URL'.
     * The name of the URL-parameter that activates debugging.
     *
     * @var string
     */
    public $smarty_debug_id = 'SMARTY_DEBUG';

    /**
     * Path of debug template.
     *
     * @var string
     */
    public $debug_tpl = null;

    /**
     * When set, smarty uses this value as error_reporting-level.
     *
     * @var int
     */
    public $error_reporting = null;

    /**#@+
     * config var settings
     */

    /**
     * Controls whether variables with the same name overwrite each other.
     *
     * @var boolean
     */
    public $config_overwrite = true;

    /**
     * Controls whether config values of on/true/yes and off/false/no get converted to boolean.
     *
     * @var boolean
     */
    public $config_booleanize = true;

    /**
     * Controls whether hidden config sections/vars are read from the file.
     *
     * @var boolean
     */
    public $config_read_hidden = false;

    /**#@-*/

    /**#@+
     * resource locking
     */

    /**
     * locking concurrent compiles
     *
     * @var boolean
     */
    public $compile_locking = true;

    /**
     * Controls whether cache resources should use locking mechanism
     *
     * @var boolean
     */
    public $cache_locking = false;

    /**
     * seconds to wait for acquiring a lock before ignoring the write lock
     *
     * @var float
     */
    public $locking_timeout = 10;

    /**#@-*/

    /**
     * resource type used if none given
     * Must be an valid key of $registered_resources.
     *
     * @var string
     */
    public $default_resource_type = 'file';

    /**
     * caching type
     * Must be an element of $cache_resource_types.
     *
     * @var string
     */
    public $caching_type = 'file';

    /**
     * config type
     *
     * @var string
     */
    public $default_config_type = 'file';

    /**
     * check If-Modified-Since headers
     *
     * @var boolean
     */
    public $cache_modified_check = false;

    /**
     * registered plugins
     *
     * @var array
     */
    public $registered_plugins = array();

    /**
     * registered objects
     *
     * @var array
     */
    public $registered_objects = array();

    /**
     * registered classes
     *
     * @var array
     */
    public $registered_classes = array();

    /**
     * registered filters
     *
     * @var array
     */
    public $registered_filters = array();

    /**
     * registered resources
     *
     * @var array
     */
    public $registered_resources = array();

    /**
     * registered cache resources
     *
     * @var array
     */
    public $registered_cache_resources = array();

    /**
     * autoload filter
     *
     * @var array
     */
    public $autoload_filters = array();

    /**
     * default modifier
     *
     * @var array
     */
    public $default_modifiers = array();

    /**
     * autoescape variable output
     *
     * @var boolean
     */
    public $escape_html = false;

    /**
     * start time for execution time calculation
     *
     * @var int
     */
    public $start_time = 0;

    /**
     * required by the compiler for BC
     *
     * @var string
     */
    public $_current_file = null;

    /**
     * internal flag to enable parser debugging
     *
     * @var bool
     */
    public $_parserdebug = false;

    /**
     * This object type (Smarty = 1, template = 2, data = 4)
     *
     * @var int
     */
    public $_objType = 1;

    /**
     * Debug object
     *
     * @var Smarty_Internal_Debug
     */
    public $_debug = null;

    /**
     * removed properties
     *
     * @var string[]
     */
    private $obsoleteProperties = array('resource_caching', 'template_resource_caching', 'direct_access_security',
                                        '_dir_perms', '_file_perms', 'plugin_search_order',
                                        'inheritance_merge_compiled_includes', 'resource_cache_mode',);

    /**
     * List of private properties which will call getter/setter on a direct access
     *
     * @var string[]
     */
    private $accessMap = array('template_dir' => 'TemplateDir', 'config_dir' => 'ConfigDir',
                               'plugins_dir' => 'PluginsDir', 'compile_dir' => 'CompileDir',
                               'cache_dir' => 'CacheDir',);

    /**#@-*/

    /**
     * Initialize new Smarty object
     */
    public function __construct()
    {
        parent::__construct();
        if (is_callable('mb_internal_encoding')) {
            mb_internal_encoding(Smarty::$_CHARSET);
        }
        $this->start_time = microtime(true);

        if (isset($_SERVER[ 'SCRIPT_NAME' ])) {
            Smarty::$global_tpl_vars[ 'SCRIPT_NAME' ] = new Smarty_Variable($_SERVER[ 'SCRIPT_NAME' ]);
        }

        // Check if we're running on windows
        Smarty::$_IS_WINDOWS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        // let PCRE (preg_*) treat strings as ISO-8859-1 if we're not dealing with UTF-8
        if (Smarty::$_CHARSET !== 'UTF-8') {
            Smarty::$_UTF8_MODIFIER = '';
        }
    }

    /**
     * Check if a template resource exists
     *
     * @param  string $resource_name template name
     *
     * @return boolean status
     */
    public function templateExists($resource_name)
    {
        // create source object
        $source = Smarty_Template_Source::load(null, $this, $resource_name);
        return $source->exists;
    }

    /**
     * Loads security class and enables security
     *
     * @param  string|Smarty_Security $security_class if a string is used, it must be class-name
     *
     * @return Smarty                 current Smarty instance for chaining
     * @throws SmartyException        when an invalid class name is provided
     */
    public function enableSecurity($security_class = null)
    {
        Smarty_Security::enableSecurity($this, $security_class);
        return $this;
    }

    /**
     * Disable security
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function disableSecurity()
    {
        $this->security_policy = null;

        return $this;
    }

    /**
     * Set template directory
     *
     * @param  string|array $template_dir directory(s) of template sources
     * @param bool          $isConfig     true for config_dir
     *
     * @return \Smarty current Smarty instance for chaining
     */
    public function setTemplateDir($template_dir, $isConfig = false)
    {
        if ($isConfig) {
            $this->config_dir = array();
            $this->_processedConfigDir = array();
        } else {
            $this->template_dir = array();
            $this->_processedTemplateDir = array();
        }
        $this->addTemplateDir($template_dir, null, $isConfig);
        return $this;
    }

    /**
     * Add template directory(s)
     *
     * @param  string|array $template_dir directory(s) of template sources
     * @param  string       $key          of the array element to assign the template dir to
     * @param bool          $isConfig     true for config_dir
     *
     * @return Smarty          current Smarty instance for chaining
     */
    public function addTemplateDir($template_dir, $key = null, $isConfig = false)
    {
        if ($isConfig) {
            $processed = &$this->_processedConfigDir;
            $dir = &$this->config_dir;
            $this->_configDirNormalized = false;
        } else {
            $processed = &$this->_processedTemplateDir;
            $dir = &$this->template_dir;
            $this->_templateDirNormalized = false;
        }
        if (is_array($template_dir)) {
            foreach ($template_dir as $k => $v) {
                if (is_int($k)) {
                    // indexes are not merged but appended
                    $dir[] = $v;
                } else {
                    // string indexes are overridden
                    $dir[ $k ] = $v;
                    unset($processed[ $key ]);
                }
            }
        } else {
            if ($key !== null) {
                // override directory at specified index
                $dir[ $key ] = $template_dir;
                unset($processed[ $key ]);
            } else {
                // append new directory
                $dir[] = $template_dir;
            }
        }
        return $this;
    }

    /**
     * Get template directories
     *
     * @param mixed $index    index of directory to get, null to get all
     * @param bool  $isConfig true for config_dir
     *
     * @return array list of template directories, or directory of $index
     */
    public function getTemplateDir($index = null, $isConfig = false)
    {
        if ($isConfig) {
            $dir = &$this->config_dir;
        } else {
            $dir = &$this->template_dir;
        }
        if ($isConfig ? !$this->_configDirNormalized : !$this->_templateDirNormalized) {
            $this->_nomalizeTemplateConfig($isConfig);
        }
        if ($index !== null) {
            return isset($dir[ $index ]) ? $dir[ $index ] : null;
        }
        return $dir;
    }

    /**
     * Set config directory
     *
     * @param $config_dir
     *
     * @return Smarty       current Smarty instance for chaining
     */
    public function setConfigDir($config_dir)
    {
        return $this->setTemplateDir($config_dir, true);
    }

    /**
     * Add config directory(s)
     *
     * @param string|array $config_dir directory(s) of config sources
     * @param mixed        $key        key of the array element to assign the config dir to
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function addConfigDir($config_dir, $key = null)
    {
        return $this->addTemplateDir($config_dir, $key, true);
    }

    /**
     * Get config directory
     *
     * @param mixed $index index of directory to get, null to get all
     *
     * @return array configuration directory
     */
    public function getConfigDir($index = null)
    {
        return $this->getTemplateDir($index, true);
    }

    /**
     * Set plugins directory
     *
     * @param  string|array $plugins_dir directory(s) of plugins
     *
     * @return Smarty       current Smarty instance for chaining
     */
    public function setPluginsDir($plugins_dir)
    {
        $this->plugins_dir = (array) $plugins_dir;
        $this->_pluginsDirNormalized = false;
        return $this;
    }

    /**
     * Adds directory of plugin files
     *
     * @param null|array $plugins_dir
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function addPluginsDir($plugins_dir)
    {
        if (empty($this->plugins_dir)) {
            $this->plugins_dir[] = SMARTY_PLUGINS_DIR;
        }
        $this->plugins_dir = array_merge($this->plugins_dir, (array) $plugins_dir);
        $this->_pluginsDirNormalized = false;
        return $this;
    }

    /**
     * Get plugin directories
     *
     * @return array list of plugin directories
     */
    public function getPluginsDir()
    {
        if (empty($this->plugins_dir)) {
            $this->plugins_dir[] = SMARTY_PLUGINS_DIR;
            $this->_pluginsDirNormalized = false;
        }
        if (!$this->_pluginsDirNormalized) {
            if (!is_array($this->plugins_dir)) {
                $this->plugins_dir = (array) $this->plugins_dir;
            }
            foreach ($this->plugins_dir as $k => $v) {
                $this->plugins_dir[ $k ] = $this->_realpath(rtrim($v, "/\\") . DS, true);
            }
            $this->_cache[ 'plugin_files' ] = array();
            $this->_pluginsDirNormalized = true;
        }
        return $this->plugins_dir;
    }

    /**
     *
     * @param  string $compile_dir directory to store compiled templates in
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function setCompileDir($compile_dir)
    {
        $this->_normalizeDir('compile_dir', $compile_dir);
        $this->_compileDirNormalized = true;
        return $this;
    }

    /**
     * Get compiled directory
     *
     * @return string path to compiled templates
     */
    public function getCompileDir()
    {
        if (!$this->_compileDirNormalized) {
            $this->_normalizeDir('compile_dir', $this->compile_dir);
            $this->_compileDirNormalized = true;
        }
        return $this->compile_dir;
    }

    /**
     * Set cache directory
     *
     * @param  string $cache_dir directory to store cached templates in
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function setCacheDir($cache_dir)
    {
        $this->_normalizeDir('cache_dir', $cache_dir);
        $this->_cacheDirNormalized = true;
        return $this;
    }

    /**
     * Get cache directory
     *
     * @return string path of cache directory
     */
    public function getCacheDir()
    {
        if (!$this->_cacheDirNormalized) {
            $this->_normalizeDir('cache_dir', $this->cache_dir);
            $this->_cacheDirNormalized = true;
        }
        return $this->cache_dir;
    }

    /**
     * Normalize and set directory string
     *
     * @param string $dirName cache_dir or compile_dir
     * @param string $dir     filepath of folder
     */
    private function _normalizeDir($dirName, $dir)
    {
        $this->{$dirName} = $this->_realpath(rtrim($dir, "/\\") . DS, true);
        if (!isset(Smarty::$_muted_directories[ $this->{$dirName} ])) {
            Smarty::$_muted_directories[ $this->{$dirName} ] = null;
        }
    }

    /**
     * Normalize template_dir or config_dir
     *
     * @param bool $isConfig true for config_dir
     *
     */
    private function _nomalizeTemplateConfig($isConfig)
    {
        if ($isConfig) {
            $processed = &$this->_processedConfigDir;
            $dir = &$this->config_dir;
        } else {
            $processed = &$this->_processedTemplateDir;
            $dir = &$this->template_dir;
        }
        if (!is_array($dir)) {
            $dir = (array) $dir;
        }
        foreach ($dir as $k => $v) {
            if (!isset($processed[ $k ])) {
                $dir[ $k ] = $v = $this->_realpath(rtrim($v, "/\\") . DS, true);
                $processed[ $k ] = true;
            }
        }
        $isConfig ? $this->_configDirNormalized = true : $this->_templateDirNormalized = true;
        $isConfig ? $this->_joined_config_dir = join('#', $this->config_dir) :
            $this->_joined_template_dir = join('#', $this->template_dir);
    }

    /**
     * creates a template object
     *
     * @param  string  $template   the resource handle of the template file
     * @param  mixed   $cache_id   cache id to be used with this template
     * @param  mixed   $compile_id compile id to be used with this template
     * @param  object  $parent     next higher level of Smarty variables
     * @param  boolean $do_clone   flag is Smarty object shall be cloned
     *
     * @return object  template object
     */
    public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
    {
        if ($cache_id !== null && (is_object($cache_id) || is_array($cache_id))) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($parent !== null && is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }
        $_templateId = $this->_getTemplateId($template, $cache_id, $compile_id);
        $tpl = null;
        if ($this->caching && isset($this->_cache[ 'isCached' ][ $_templateId ])) {
            $tpl = $do_clone ? clone $this->_cache[ 'isCached' ][ $_templateId ] :
                $this->_cache[ 'isCached' ][ $_templateId ];
            $tpl->tpl_vars = $tpl->config_vars = array();
        } else if (!$do_clone && isset($this->_cache[ 'tplObjects' ][ $_templateId ])) {
            $tpl = clone $this->_cache[ 'tplObjects' ][ $_templateId ];
        } else {
            /* @var Smarty_Internal_Template $tpl */
            $tpl = new $this->template_class($template, $this, null, $cache_id, $compile_id, null, null);
            $tpl->templateId = $_templateId;
        }
        if ($do_clone) {
            $tpl->smarty = clone $tpl->smarty;
        }
        $tpl->parent = $parent ? $parent : $this;
        // fill data if present
        if (!empty($data) && is_array($data)) {
            // set up variable values
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[ $_key ] = new Smarty_Variable($_val);
            }
        }
        if ($this->debugging || $this->debugging_ctrl == 'URL') {
            $tpl->smarty->_debug = new Smarty_Internal_Debug();
            // check URL debugging control
            if (!$this->debugging && $this->debugging_ctrl == 'URL') {
                $tpl->smarty->_debug->debugUrl($tpl->smarty);
            }
        }
        return $tpl;
    }

    /**
     * Takes unknown classes and loads plugin files for them
     * class name format: Smarty_PluginType_PluginName
     * plugin filename format: plugintype.pluginname.php
     *
     * @param  string $plugin_name class plugin name to load
     * @param  bool   $check       check if already loaded
     *
     * @throws SmartyException
     * @return string |boolean filepath of loaded file or false
     */
    public function loadPlugin($plugin_name, $check = true)
    {
        return $this->ext->loadPlugin->loadPlugin($this, $plugin_name, $check);
    }

    /**
     * Get unique template id
     *
     * @param string                    $template_name
     * @param null|mixed                $cache_id
     * @param null|mixed                $compile_id
     * @param null                      $caching
     * @param \Smarty_Internal_Template $template
     *
     * @return string
     */
    public function _getTemplateId($template_name, $cache_id = null, $compile_id = null, $caching = null,
                                   Smarty_Internal_Template $template = null)
    {
        $template_name = (strpos($template_name, ':') === false) ? "{$this->default_resource_type}:{$template_name}" :
            $template_name;
        $cache_id = $cache_id === null ? $this->cache_id : $cache_id;
        $compile_id = $compile_id === null ? $this->compile_id : $compile_id;
        $caching = (int) ($caching === null ? $this->caching : $caching);

        if ((isset($template) && strpos($template_name, ':.') !== false) || $this->allow_ambiguous_resources) {
            $_templateId =
                Smarty_Resource::getUniqueTemplateName((isset($template) ? $template : $this), $template_name) .
                "#{$cache_id}#{$compile_id}#{$caching}";
        } else {
            $_templateId = $this->_joined_template_dir . "#{$template_name}#{$cache_id}#{$compile_id}#{$caching}";
        }
        if (isset($_templateId[ 150 ])) {
            $_templateId = sha1($_templateId);
        }
        return $_templateId;
    }

    /**
     * Normalize path
     *  - remove /./ and /../
     *  - make it absolute if required
     *
     * @param string $path      file path
     * @param bool   $realpath  if true - convert to absolute
     *                          false - convert to relative
     *                          null - keep as it is but remove /./ /../
     *
     * @return string
     */
    public function _realpath($path, $realpath = null)
    {
        $nds = DS == '/' ? '\\' : '/';
        // normalize DS 
        $path = str_replace($nds, DS, $path);
        preg_match('%^(?<root>(?:[[:alpha:]]:[\\\\]|/|[\\\\]{2}[[:alpha:]]+|[[:print:]]{2,}:[/]{2}|[\\\\])?)(?<path>(?:[[:print:]]*))$%',
                   $path, $parts);
        $path = $parts[ 'path' ];
        if ($parts[ 'root' ] == '\\') {
            $parts[ 'root' ] = substr(getcwd(), 0, 2) . $parts[ 'root' ];
        } else {
            if ($realpath !== null && !$parts[ 'root' ]) {
                $path = getcwd() . DS . $path;
            }
        }
        // remove noop 'DS DS' and 'DS.DS' patterns
        $path = preg_replace('#([\\\\/]([.]?[\\\\/])+)#', DS, $path);
        // resolve '..DS' pattern, smallest first
        if (strpos($path, '..' . DS) != false &&
            preg_match_all('#(([.]?[\\\\/])*([.][.])[\\\\/]([.]?[\\\\/])*)+#', $path, $match)
        ) {
            $counts = array();
            foreach ($match[ 0 ] as $m) {
                $counts[] = (int) ((strlen($m) - 1) / 3);
            }
            sort($counts);
            foreach ($counts as $count) {
                $path = preg_replace('#(([\\\\/]([.]?[\\\\/])*[^\\\\/.]+){' . $count .
                                     '}[\\\\/]([.]?[\\\\/])*([.][.][\\\\/]([.]?[\\\\/])*){' . $count . '})(?=[^.])#',
                                     DS, $path);
            }
        }

        return $parts[ 'root' ] . $path;
    }

    /**
     * Empty template objects cache
     */
    public function _clearTemplateCache()
    {
        $this->_cache[ 'isCached' ] = array();
        $this->_cache[ 'tplObjects' ] = array();
    }

    /**
     * @param boolean $compile_check
     */
    public function setCompileCheck($compile_check)
    {
        $this->compile_check = $compile_check;
    }

    /**
     * @param boolean $use_sub_dirs
     */
    public function setUseSubDirs($use_sub_dirs)
    {
        $this->use_sub_dirs = $use_sub_dirs;
    }

    /**
     * @param int $error_reporting
     */
    public function setErrorReporting($error_reporting)
    {
        $this->error_reporting = $error_reporting;
    }

    /**
     * @param boolean $escape_html
     */
    public function setEscapeHtml($escape_html)
    {
        $this->escape_html = $escape_html;
    }

    /**
     * @param boolean $auto_literal
     */
    public function setAutoLiteral($auto_literal)
    {
        $this->auto_literal = $auto_literal;
    }

    /**
     * @param boolean $force_compile
     */
    public function setForceCompile($force_compile)
    {
        $this->force_compile = $force_compile;
    }

    /**
     * @param boolean $merge_compiled_includes
     */
    public function setMergeCompiledIncludes($merge_compiled_includes)
    {
        $this->merge_compiled_includes = $merge_compiled_includes;
    }

    /**
     * @param string $left_delimiter
     */
    public function setLeftDelimiter($left_delimiter)
    {
        $this->left_delimiter = $left_delimiter;
    }

    /**
     * @param string $right_delimiter
     */
    public function setRightDelimiter($right_delimiter)
    {
        $this->right_delimiter = $right_delimiter;
    }

    /**
     * @param boolean $debugging
     */
    public function setDebugging($debugging)
    {
        $this->debugging = $debugging;
    }

    /**
     * @param boolean $config_overwrite
     */
    public function setConfigOverwrite($config_overwrite)
    {
        $this->config_overwrite = $config_overwrite;
    }

    /**
     * @param boolean $config_booleanize
     */
    public function setConfigBooleanize($config_booleanize)
    {
        $this->config_booleanize = $config_booleanize;
    }

    /**
     * @param boolean $config_read_hidden
     */
    public function setConfigReadHidden($config_read_hidden)
    {
        $this->config_read_hidden = $config_read_hidden;
    }

    /**
     * @param boolean $compile_locking
     */
    public function setCompileLocking($compile_locking)
    {
        $this->compile_locking = $compile_locking;
    }

    /**
     * @param string $default_resource_type
     */
    public function setDefaultResourceType($default_resource_type)
    {
        $this->default_resource_type = $default_resource_type;
    }

    /**
     * @param string $caching_type
     */
    public function setCachingType($caching_type)
    {
        $this->caching_type = $caching_type;
    }

    /**
     * Test install
     *
     * @param null $errors
     */
    public function testInstall(&$errors = null)
    {
        Smarty_Internal_TestInstall::testInstall($this, $errors);
    }

    /**
     * <<magic>> Generic getter.
     * Calls the appropriate getter function.
     * Issues an E_USER_NOTICE if no valid getter is found.
     *
     * @param  string $name property name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->accessMap[ $name ])) {
            $method = 'get' . $this->accessMap[ $name ];
            return $this->{$method}();
        } elseif (isset($this->_cache[ $name ])) {
            return $this->_cache[ $name ];
        } elseif (in_array($name, $this->obsoleteProperties)) {
            return null;
        } else {
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
        }
        return null;
    }

    /**
     * <<magic>> Generic setter.
     * Calls the appropriate setter function.
     * Issues an E_USER_NOTICE if no valid setter is found.
     *
     * @param string $name  property name
     * @param mixed  $value parameter passed to setter
     */
    public function __set($name, $value)
    {
        if (isset($this->accessMap[ $name ])) {
            $method = 'set' . $this->accessMap[ $name ];
            $this->{$method}($value);
        } elseif (in_array($name, $this->obsoleteProperties)) {
            return;
        } else {
            if (is_object($value) && method_exists($value, $name)) {
                $this->$name = $value;
            } else {
                trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
            }
        }
    }

    /**
     * Error Handler to mute expected messages
     *
     * @link http://php.net/set_error_handler
     *
     * @param  integer $errno Error level
     * @param          $errstr
     * @param          $errfile
     * @param          $errline
     * @param          $errcontext
     *
     * @return bool|void
     */
    public static function mutingErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $_is_muted_directory = false;

        // add the SMARTY_DIR to the list of muted directories
        if (!isset(Smarty::$_muted_directories[ SMARTY_DIR ])) {
            $smarty_dir = realpath(SMARTY_DIR);
            if ($smarty_dir !== false) {
                Smarty::$_muted_directories[ SMARTY_DIR ] =
                    array('file' => $smarty_dir, 'length' => strlen($smarty_dir),);
            }
        }

        // walk the muted directories and test against $errfile
        foreach (Smarty::$_muted_directories as $key => &$dir) {
            if (!$dir) {
                // resolve directory and length for speedy comparisons
                $file = realpath($key);
                if ($file === false) {
                    // this directory does not exist, remove and skip it
                    unset(Smarty::$_muted_directories[ $key ]);
                    continue;
                }
                $dir = array('file' => $file, 'length' => strlen($file),);
            }
            if (!strncmp($errfile, $dir[ 'file' ], $dir[ 'length' ])) {
                $_is_muted_directory = true;
                break;
            }
        }
        // pass to next error handler if this error did not occur inside SMARTY_DIR
        // or the error was within smarty but masked to be ignored
        if (!$_is_muted_directory || ($errno && $errno & error_reporting())) {
            if (Smarty::$_previous_error_handler) {
                return call_user_func(Smarty::$_previous_error_handler, $errno, $errstr, $errfile, $errline,
                                      $errcontext);
            } else {
                return false;
            }
        }
        return;
    }

    /**
     * Enable error handler to mute expected messages
     *
     * @return void
     */
    public static function muteExpectedErrors()
    {
        /*
            error muting is done because some people implemented custom error_handlers using
            http://php.net/set_error_handler and for some reason did not understand the following paragraph:

                It is important to remember that the standard PHP error handler is completely bypassed for the
                error types specified by error_types unless the callback function returns FALSE.
                error_reporting() settings will have no effect and your error handler will be called regardless -
                however you are still able to read the current value of error_reporting and act appropriately.
                Of particular note is that this value will be 0 if the statement that caused the error was
                prepended by the @ error-control operator.

            Smarty deliberately uses @filemtime() over file_exists() and filemtime() in some places. Reasons include
                - @filemtime() is almost twice as fast as using an additional file_exists()
                - between file_exists() and filemtime() a possible race condition is opened,
                  which does not exist using the simple @filemtime() approach.
        */
        $error_handler = array('Smarty', 'mutingErrorHandler');
        $previous = set_error_handler($error_handler);

        // avoid dead loops
        if ($previous !== $error_handler) {
            Smarty::$_previous_error_handler = $previous;
        }
    }

    /**
     * Disable error handler muting expected messages
     *
     * @return void
     */
    public static function unmuteExpectedErrors()
    {
        restore_error_handler();
    }/**
 * @Author: Administrator
 * @Date:   2016-09-07 23:53:08
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-09 23:48:26
 */
class SmartyView {
	private static $smarty = NULL;

	public function __construct(){
		if(!empty(self::$smarty)) return;
		$smarty = new Smarty();

		$smarty->template_dir = APP_TPL_PATH . '/' .CONTROLLER .'/';
		$smarty->compile_dir = APP_COMPILE_PATH;
		$smarty->cache_dir = APP_CACHE_PATH;

		$smarty->left_delimiter = C('LEFT_DELIMITER');
		$smarty->right_delimiter = C('RIGHT_DELIMITER');
		$smarty->caching = C('CACHE_ON');
		$smarty->cache_lifetime =C('CACHE_TIME');

		  // p($smarty);

		self::$smarty = $smarty;
	}

	protected function display($tpl){
		// echo 111;
		self::$smarty -> display($tpl);
	}

	protected function assign($name,$value){
		// echo 111;
		self::$smarty->assign($name,$value);
	}

	protected function isCached($tpl=NULL){
		if(!C('SMARTY_ON')) E('请先开启Smarty');
		$tpl = $this->get_tpl($tpl);
		return self::$smarty->isCached($tpl);
	}p /**
 * @Author: Administrator
 * @Date:   2016-07-24 16:59:31
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-09 23:41:21
 */
//父类controller
class Controller extends SmartyView{
	private $var = array();

	public function __construct(){
		//父类构造器
		if(C('SMARTY_ON')){
			parent::__construct();
		}
		if(method_exists($this, '__init'))
			$this->__init();
		if(method_exists($this, '__auto'))
			$this->__auto();
	}
	/**
	 * [success 成功跳转页面]
	 * @param  [type]  $msg  [错误信息]
	 * @param  [type]  $url  [跳转url]
	 * @param  integer $time [提示时间]
	 * @return [type]        [description]
	 */
	protected function success($msg,$url=NULL,$time=3){
		$url=$url ? "window.location.href='". $url . "'" : "window.history.back(-1)";
		include APP_TPL_PATH . '/success.html';
	}
	/**
	 * [error 失败跳转页面]
	 * @param  [type]  $msg  [错误信息]
	 * @param  [type]  $url  [跳转url]
	 * @param  integer $time [提示时间]
	 * @return [type]        [description]
	 */
	protected function error($msg,$url=NULL,$time=3){
		$url=$url ? "window.location.href='". $url . "'" : 'window.history.back(-1)';
		echo $url;
		include APP_TPL_PATH . '/error.html';
	}

	// protected function display($tpl=NULL)
	// {
	// 	if(is_null($tpl)){
	// 		$path = APP_TPL_PATH .'/' . CONTROLLER .'/' .ACTION.'.html';
	// 		// echo $path;
	// 	}else{
	// 		$suffix = strrchr($tpl, '.');
	// 		$tpl = empty($suffix) ? $tpl . '.html' : $tpl;
	// 		$path = APP_TPL_PATH .'/'.CONTROLLER .'/'.$tpl;
	// 	}

	// 	if(!is_file($path)) E($path.'模板不存在');
	// 	extract($this->var);
	// 	include $path;
	// }
	// 就是插件，这样你就可以给OT增加很多功能，要想增加功能就得修改源程序啊，但是如果cms升级了，你的增加功能就被覆盖了，于是就出现了钩子，这样你就可以在模板里某个位置加入钩子标签，因为模版不怕被修改。大概就这么个意思了
	// 
	protected function get_tpl($tpl){
		if(is_null($tpl)){
			$path = APP_TPL_PATH . '/' .CONTROLLER .'/' .ACTION .'.html';
		}else{
			$suffix = strchr($tpl,'.');
			$tpl = empty($suffix) ? $tpl .'.html' : $tpl;;
			$path = APP_TPL_PATH . '/' . CONTROLLER . '/' .$tpl;
		}
		return $path;
	}

	protected function display($tpl=NULL){
		$path = $this->get_tpl($tpl);
		if(!file_exists($path)) E($path.'模板不存在');

		if(C('SMARTY_ON')){
			parent::display($path);
		}else{
			extract($this->var);
			include $path;
		}
	}

	protected function assign($name,$value){

		if (C('SMARTY_ON')) {
			parent::assign($name,$value);
		}else{
			if(is_array($name)){
				$this->var = array_merge($this->var , $name);
			}
			$this->var[$name] = $value; 
		}
	}
}/**
 * @Author: Administrator
 * @Date:   2016-07-23 10:47:19
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-09 23:11:05
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
					require_once COMMON_LIB_PATH.'/'. $af;
					// require_once $autoPath;
				}
			}
		}
	}<?php
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

	}/**
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
 }/**
 * Project:     Smarty: the PHP compiling template engine
 * File:        Smarty.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * For questions, help, comments, discussion, etc., please join the
 * Smarty mailing list. Send a blank e-mail to
 * smarty-discussion-subscribe@googlegroups.com
 *
 * @link      http://www.smarty.net/
 * @copyright 2016 New Digital Group, Inc.
 * @copyright 2016 Uwe Tews
 * @author    Monte Ohrt <monte at ohrt dot com>
 * @author    Uwe Tews
 * @author    Rodney Rehm
 * @package   Smarty
 * @version   3.1.30
 */

/**
 * define shorthand directory separator constant
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * set SMARTY_DIR to absolute path to Smarty library files.
 * Sets SMARTY_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DS);
}

/**
 * set SMARTY_SYSPLUGINS_DIR to absolute path to Smarty internal plugins.
 * Sets SMARTY_SYSPLUGINS_DIR only if user application has not already defined it.
 */
if (!defined('SMARTY_SYSPLUGINS_DIR')) {
    define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins' . DS);
}
if (!defined('SMARTY_PLUGINS_DIR')) {
    define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins' . DS);
}
if (!defined('SMARTY_MBSTRING')) {
    define('SMARTY_MBSTRING', function_exists('mb_get_info'));
}
if (!defined('SMARTY_RESOURCE_CHAR_SET')) {
    // UTF-8 can only be done properly when mbstring is available!
    /**
     * @deprecated in favor of Smarty::$_CHARSET
     */
    define('SMARTY_RESOURCE_CHAR_SET', SMARTY_MBSTRING ? 'UTF-8' : 'ISO-8859-1');
}
if (!defined('SMARTY_RESOURCE_DATE_FORMAT')) {
    /**
     * @deprecated in favor of Smarty::$_DATE_FORMAT
     */
    define('SMARTY_RESOURCE_DATE_FORMAT', '%b %e, %Y');
}

/**
 * Try loading the Smarty_Internal_Data class
 * If we fail we must load Smarty's autoloader.
 * Otherwise we may have a global autoloader like Composer
 */
if (!class_exists('Smarty_Autoloader', false)) {
    if (!class_exists('Smarty_Internal_Data', true)) {
        require_once dirname(__FILE__) . '/Autoloader.php';
        Smarty_Autoloader::registerBC();
    }
}

/**
 * Load always needed external class files
 */
if (!class_exists('Smarty_Internal_Data', false)) {
    require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_data.php';
}
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_extension_handler.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_templatebase.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_internal_template.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_resource.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_variable.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_template_source.php';
require_once SMARTY_SYSPLUGINS_DIR . 'smarty_template_resource_base.php';

/**
 * This is the main Smarty class
 *
 * @package Smarty
 *
 * The following methods will be dynamically loaded by the extension handler when they are called.
 * They are located in a corresponding Smarty_Internal_Method_xxxx class
 *
 * @method int clearAllCache(int $exp_time = null, string $type = null)
 * @method int clearCache(string $template_name, string $cache_id = null, string $compile_id = null, int $exp_time = null, string $type = null)
 * @method int compileAllTemplates(string $extension = '.tpl', bool $force_compile = false, int $time_limit = 0, int $max_errors = null)
 * @method int compileAllConfig(string $extension = '.conf', bool $force_compile = false, int $time_limit = 0, int $max_errors = null)
 * @method int clearCompiledTemplate($resource_name = null, $compile_id = null, $exp_time = null)
 */
class Smarty extends Smarty_Internal_TemplateBase
{
    /**#@+
     * constant definitions
     */

    /**
     * smarty version
     */
    const SMARTY_VERSION = '3.1.30';

    /**
     * define variable scopes
     */
    const SCOPE_LOCAL = 1;

    const SCOPE_PARENT = 2;

    const SCOPE_TPL_ROOT = 4;

    const SCOPE_ROOT = 8;

    const SCOPE_SMARTY = 16;

    const SCOPE_GLOBAL = 32;

    /**
     * define caching modes
     */
    const CACHING_OFF = 0;

    const CACHING_LIFETIME_CURRENT = 1;

    const CACHING_LIFETIME_SAVED = 2;

    /**
     * define constant for clearing cache files be saved expiration dates
     */
    const CLEAR_EXPIRED = - 1;

    /**
     * define compile check modes
     */
    const COMPILECHECK_OFF = 0;

    const COMPILECHECK_ON = 1;

    const COMPILECHECK_CACHEMISS = 2;

    /**
     * define debug modes
     */
    const DEBUG_OFF = 0;

    const DEBUG_ON = 1;

    const DEBUG_INDIVIDUAL = 2;

    /**
     * modes for handling of "<?php ... ?>" tags in templates.
     */
    const PHP_PASSTHRU = 0; //-> print tags as plain text

    const PHP_QUOTE = 1; //-> escape tags as entities

    const PHP_REMOVE = 2; //-> escape tags as entities

    const PHP_ALLOW = 3; //-> escape tags as entities

    /**
     * filter types
     */
    const FILTER_POST = 'post';

    const FILTER_PRE = 'pre';

    const FILTER_OUTPUT = 'output';

    const FILTER_VARIABLE = 'variable';

    /**
     * plugin types
     */
    const PLUGIN_FUNCTION = 'function';

    const PLUGIN_BLOCK = 'block';

    const PLUGIN_COMPILER = 'compiler';

    const PLUGIN_MODIFIER = 'modifier';

    const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';

    /**
     * Resource caching modes
     * (not used since 3.1.30)
     */
    const RESOURCE_CACHE_OFF = 0;

    const RESOURCE_CACHE_AUTOMATIC = 1; // cache template objects by rules

    const RESOURCE_CACHE_TEMPLATE = 2; // cache all template objects

    const RESOURCE_CACHE_ON = 4;    // cache source and compiled resources

    /**#@-*/

    /**
     * assigned global tpl vars
     */
    public static $global_tpl_vars = array();

    /**
     * error handler returned by set_error_handler() in Smarty::muteExpectedErrors()
     */
    public static $_previous_error_handler = null;

    /**
     * contains directories outside of SMARTY_DIR that are to be muted by muteExpectedErrors()
     */
    public static $_muted_directories = array();

    /**
     * Flag denoting if Multibyte String functions are available
     */
    public static $_MBSTRING = SMARTY_MBSTRING;

    /**
     * The character set to adhere to (e.g. "UTF-8")
     */
    public static $_CHARSET = SMARTY_RESOURCE_CHAR_SET;

    /**
     * The date format to be used internally
     * (accepts date() and strftime())
     */
    public static $_DATE_FORMAT = SMARTY_RESOURCE_DATE_FORMAT;

    /**
     * Flag denoting if PCRE should run in UTF-8 mode
     */
    public static $_UTF8_MODIFIER = 'u';

    /**
     * Flag denoting if operating system is windows
     */
    public static $_IS_WINDOWS = false;

    /**#@+
     * variables
     */

    /**
     * auto literal on delimiters with whitespace
     *
     * @var boolean
     */
    public $auto_literal = true;

    /**
     * display error on not assigned variables
     *
     * @var boolean
     */
    public $error_unassigned = false;

    /**
     * look up relative file path in include_path
     *
     * @var boolean
     */
    public $use_include_path = false;

    /**
     * template directory
     *
     * @var array
     */
    protected $template_dir = array('./templates/');

    /**
     * flags for normalized template directory entries
     *
     * @var array
     */
    protected $_processedTemplateDir = array();

    /**
     * flag if template_dir is normalized
     *
     * @var bool
     */
    public $_templateDirNormalized = false;

    /**
     * joined template directory string used in cache keys
     *
     * @var string
     */
    public $_joined_template_dir = null;

    /**
     * config directory
     *
     * @var array
     */
    protected $config_dir = array('./configs/');

    /**
     * flags for normalized template directory entries
     *
     * @var array
     */
    protected $_processedConfigDir = array();

    /**
     * flag if config_dir is normalized
     *
     * @var bool
     */
    public $_configDirNormalized = false;

    /**
     * joined config directory string used in cache keys
     *
     * @var string
     */
    public $_joined_config_dir = null;

    /**
     * default template handler
     *
     * @var callable
     */
    public $default_template_handler_func = null;

    /**
     * default config handler
     *
     * @var callable
     */
    public $default_config_handler_func = null;

    /**
     * default plugin handler
     *
     * @var callable
     */
    public $default_plugin_handler_func = null;

    /**
     * compile directory
     *
     * @var string
     */
    protected $compile_dir = './templates_c/';

    /**
     * flag if template_dir is normalized
     *
     * @var bool
     */
    public $_compileDirNormalized = false;

    /**
     * plugins directory
     *
     * @var array
     */
    protected $plugins_dir = array();

    /**
     * flag if plugins_dir is normalized
     *
     * @var bool
     */
    public $_pluginsDirNormalized = false;

    /**
     * cache directory
     *
     * @var string
     */
    protected $cache_dir = './cache/';

    /**
     * flag if template_dir is normalized
     *
     * @var bool
     */
    public $_cacheDirNormalized = false;

    /**
     * force template compiling?
     *
     * @var boolean
     */
    public $force_compile = false;

    /**
     * check template for modifications?
     *
     * @var boolean
     */
    public $compile_check = true;

    /**
     * use sub dirs for compiled/cached files?
     *
     * @var boolean
     */
    public $use_sub_dirs = false;

    /**
     * allow ambiguous resources (that are made unique by the resource handler)
     *
     * @var boolean
     */
    public $allow_ambiguous_resources = false;

    /**
     * merge compiled includes
     *
     * @var boolean
     */
    public $merge_compiled_includes = false;

    /**
     * force cache file creation
     *
     * @var boolean
     */
    public $force_cache = false;

    /**
     * template left-delimiter
     *
     * @var string
     */
    public $left_delimiter = "{";

    /**
     * template right-delimiter
     *
     * @var string
     */
    public $right_delimiter = "}";

    /**#@+
     * security
     */
    /**
     * class name
     * This should be instance of Smarty_Security.
     *
     * @var string
     * @see Smarty_Security
     */
    public $security_class = 'Smarty_Security';

    /**
     * implementation of security class
     *
     * @var Smarty_Security
     */
    public $security_policy = null;

    /**
     * controls handling of PHP-blocks
     *
     * @var integer
     */
    public $php_handling = self::PHP_PASSTHRU;

    /**
     * controls if the php template file resource is allowed
     *
     * @var bool
     */
    public $allow_php_templates = false;

    /**#@-*/
    /**
     * debug mode
     * Setting this to true enables the debug-console.
     *
     * @var boolean
     */
    public $debugging = false;

    /**
     * This determines if debugging is enable-able from the browser.
     * <ul>
     *  <li>NONE => no debugging control allowed</li>
     *  <li>URL => enable debugging when SMARTY_DEBUG is found in the URL.</li>
     * </ul>
     *
     * @var string
     */
    public $debugging_ctrl = 'NONE';

    /**
     * Name of debugging URL-param.
     * Only used when $debugging_ctrl is set to 'URL'.
     * The name of the URL-parameter that activates debugging.
     *
     * @var string
     */
    public $smarty_debug_id = 'SMARTY_DEBUG';

    /**
     * Path of debug template.
     *
     * @var string
     */
    public $debug_tpl = null;

    /**
     * When set, smarty uses this value as error_reporting-level.
     *
     * @var int
     */
    public $error_reporting = null;

    /**#@+
     * config var settings
     */

    /**
     * Controls whether variables with the same name overwrite each other.
     *
     * @var boolean
     */
    public $config_overwrite = true;

    /**
     * Controls whether config values of on/true/yes and off/false/no get converted to boolean.
     *
     * @var boolean
     */
    public $config_booleanize = true;

    /**
     * Controls whether hidden config sections/vars are read from the file.
     *
     * @var boolean
     */
    public $config_read_hidden = false;

    /**#@-*/

    /**#@+
     * resource locking
     */

    /**
     * locking concurrent compiles
     *
     * @var boolean
     */
    public $compile_locking = true;

    /**
     * Controls whether cache resources should use locking mechanism
     *
     * @var boolean
     */
    public $cache_locking = false;

    /**
     * seconds to wait for acquiring a lock before ignoring the write lock
     *
     * @var float
     */
    public $locking_timeout = 10;

    /**#@-*/

    /**
     * resource type used if none given
     * Must be an valid key of $registered_resources.
     *
     * @var string
     */
    public $default_resource_type = 'file';

    /**
     * caching type
     * Must be an element of $cache_resource_types.
     *
     * @var string
     */
    public $caching_type = 'file';

    /**
     * config type
     *
     * @var string
     */
    public $default_config_type = 'file';

    /**
     * check If-Modified-Since headers
     *
     * @var boolean
     */
    public $cache_modified_check = false;

    /**
     * registered plugins
     *
     * @var array
     */
    public $registered_plugins = array();

    /**
     * registered objects
     *
     * @var array
     */
    public $registered_objects = array();

    /**
     * registered classes
     *
     * @var array
     */
    public $registered_classes = array();

    /**
     * registered filters
     *
     * @var array
     */
    public $registered_filters = array();

    /**
     * registered resources
     *
     * @var array
     */
    public $registered_resources = array();

    /**
     * registered cache resources
     *
     * @var array
     */
    public $registered_cache_resources = array();

    /**
     * autoload filter
     *
     * @var array
     */
    public $autoload_filters = array();

    /**
     * default modifier
     *
     * @var array
     */
    public $default_modifiers = array();

    /**
     * autoescape variable output
     *
     * @var boolean
     */
    public $escape_html = false;

    /**
     * start time for execution time calculation
     *
     * @var int
     */
    public $start_time = 0;

    /**
     * required by the compiler for BC
     *
     * @var string
     */
    public $_current_file = null;

    /**
     * internal flag to enable parser debugging
     *
     * @var bool
     */
    public $_parserdebug = false;

    /**
     * This object type (Smarty = 1, template = 2, data = 4)
     *
     * @var int
     */
    public $_objType = 1;

    /**
     * Debug object
     *
     * @var Smarty_Internal_Debug
     */
    public $_debug = null;

    /**
     * removed properties
     *
     * @var string[]
     */
    private $obsoleteProperties = array('resource_caching', 'template_resource_caching', 'direct_access_security',
                                        '_dir_perms', '_file_perms', 'plugin_search_order',
                                        'inheritance_merge_compiled_includes', 'resource_cache_mode',);

    /**
     * List of private properties which will call getter/setter on a direct access
     *
     * @var string[]
     */
    private $accessMap = array('template_dir' => 'TemplateDir', 'config_dir' => 'ConfigDir',
                               'plugins_dir' => 'PluginsDir', 'compile_dir' => 'CompileDir',
                               'cache_dir' => 'CacheDir',);

    /**#@-*/

    /**
     * Initialize new Smarty object
     */
    public function __construct()
    {
        parent::__construct();
        if (is_callable('mb_internal_encoding')) {
            mb_internal_encoding(Smarty::$_CHARSET);
        }
        $this->start_time = microtime(true);

        if (isset($_SERVER[ 'SCRIPT_NAME' ])) {
            Smarty::$global_tpl_vars[ 'SCRIPT_NAME' ] = new Smarty_Variable($_SERVER[ 'SCRIPT_NAME' ]);
        }

        // Check if we're running on windows
        Smarty::$_IS_WINDOWS = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        // let PCRE (preg_*) treat strings as ISO-8859-1 if we're not dealing with UTF-8
        if (Smarty::$_CHARSET !== 'UTF-8') {
            Smarty::$_UTF8_MODIFIER = '';
        }
    }

    /**
     * Check if a template resource exists
     *
     * @param  string $resource_name template name
     *
     * @return boolean status
     */
    public function templateExists($resource_name)
    {
        // create source object
        $source = Smarty_Template_Source::load(null, $this, $resource_name);
        return $source->exists;
    }

    /**
     * Loads security class and enables security
     *
     * @param  string|Smarty_Security $security_class if a string is used, it must be class-name
     *
     * @return Smarty                 current Smarty instance for chaining
     * @throws SmartyException        when an invalid class name is provided
     */
    public function enableSecurity($security_class = null)
    {
        Smarty_Security::enableSecurity($this, $security_class);
        return $this;
    }

    /**
     * Disable security
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function disableSecurity()
    {
        $this->security_policy = null;

        return $this;
    }

    /**
     * Set template directory
     *
     * @param  string|array $template_dir directory(s) of template sources
     * @param bool          $isConfig     true for config_dir
     *
     * @return \Smarty current Smarty instance for chaining
     */
    public function setTemplateDir($template_dir, $isConfig = false)
    {
        if ($isConfig) {
            $this->config_dir = array();
            $this->_processedConfigDir = array();
        } else {
            $this->template_dir = array();
            $this->_processedTemplateDir = array();
        }
        $this->addTemplateDir($template_dir, null, $isConfig);
        return $this;
    }

    /**
     * Add template directory(s)
     *
     * @param  string|array $template_dir directory(s) of template sources
     * @param  string       $key          of the array element to assign the template dir to
     * @param bool          $isConfig     true for config_dir
     *
     * @return Smarty          current Smarty instance for chaining
     */
    public function addTemplateDir($template_dir, $key = null, $isConfig = false)
    {
        if ($isConfig) {
            $processed = &$this->_processedConfigDir;
            $dir = &$this->config_dir;
            $this->_configDirNormalized = false;
        } else {
            $processed = &$this->_processedTemplateDir;
            $dir = &$this->template_dir;
            $this->_templateDirNormalized = false;
        }
        if (is_array($template_dir)) {
            foreach ($template_dir as $k => $v) {
                if (is_int($k)) {
                    // indexes are not merged but appended
                    $dir[] = $v;
                } else {
                    // string indexes are overridden
                    $dir[ $k ] = $v;
                    unset($processed[ $key ]);
                }
            }
        } else {
            if ($key !== null) {
                // override directory at specified index
                $dir[ $key ] = $template_dir;
                unset($processed[ $key ]);
            } else {
                // append new directory
                $dir[] = $template_dir;
            }
        }
        return $this;
    }

    /**
     * Get template directories
     *
     * @param mixed $index    index of directory to get, null to get all
     * @param bool  $isConfig true for config_dir
     *
     * @return array list of template directories, or directory of $index
     */
    public function getTemplateDir($index = null, $isConfig = false)
    {
        if ($isConfig) {
            $dir = &$this->config_dir;
        } else {
            $dir = &$this->template_dir;
        }
        if ($isConfig ? !$this->_configDirNormalized : !$this->_templateDirNormalized) {
            $this->_nomalizeTemplateConfig($isConfig);
        }
        if ($index !== null) {
            return isset($dir[ $index ]) ? $dir[ $index ] : null;
        }
        return $dir;
    }

    /**
     * Set config directory
     *
     * @param $config_dir
     *
     * @return Smarty       current Smarty instance for chaining
     */
    public function setConfigDir($config_dir)
    {
        return $this->setTemplateDir($config_dir, true);
    }

    /**
     * Add config directory(s)
     *
     * @param string|array $config_dir directory(s) of config sources
     * @param mixed        $key        key of the array element to assign the config dir to
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function addConfigDir($config_dir, $key = null)
    {
        return $this->addTemplateDir($config_dir, $key, true);
    }

    /**
     * Get config directory
     *
     * @param mixed $index index of directory to get, null to get all
     *
     * @return array configuration directory
     */
    public function getConfigDir($index = null)
    {
        return $this->getTemplateDir($index, true);
    }

    /**
     * Set plugins directory
     *
     * @param  string|array $plugins_dir directory(s) of plugins
     *
     * @return Smarty       current Smarty instance for chaining
     */
    public function setPluginsDir($plugins_dir)
    {
        $this->plugins_dir = (array) $plugins_dir;
        $this->_pluginsDirNormalized = false;
        return $this;
    }

    /**
     * Adds directory of plugin files
     *
     * @param null|array $plugins_dir
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function addPluginsDir($plugins_dir)
    {
        if (empty($this->plugins_dir)) {
            $this->plugins_dir[] = SMARTY_PLUGINS_DIR;
        }
        $this->plugins_dir = array_merge($this->plugins_dir, (array) $plugins_dir);
        $this->_pluginsDirNormalized = false;
        return $this;
    }

    /**
     * Get plugin directories
     *
     * @return array list of plugin directories
     */
    public function getPluginsDir()
    {
        if (empty($this->plugins_dir)) {
            $this->plugins_dir[] = SMARTY_PLUGINS_DIR;
            $this->_pluginsDirNormalized = false;
        }
        if (!$this->_pluginsDirNormalized) {
            if (!is_array($this->plugins_dir)) {
                $this->plugins_dir = (array) $this->plugins_dir;
            }
            foreach ($this->plugins_dir as $k => $v) {
                $this->plugins_dir[ $k ] = $this->_realpath(rtrim($v, "/\\") . DS, true);
            }
            $this->_cache[ 'plugin_files' ] = array();
            $this->_pluginsDirNormalized = true;
        }
        return $this->plugins_dir;
    }

    /**
     *
     * @param  string $compile_dir directory to store compiled templates in
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function setCompileDir($compile_dir)
    {
        $this->_normalizeDir('compile_dir', $compile_dir);
        $this->_compileDirNormalized = true;
        return $this;
    }

    /**
     * Get compiled directory
     *
     * @return string path to compiled templates
     */
    public function getCompileDir()
    {
        if (!$this->_compileDirNormalized) {
            $this->_normalizeDir('compile_dir', $this->compile_dir);
            $this->_compileDirNormalized = true;
        }
        return $this->compile_dir;
    }

    /**
     * Set cache directory
     *
     * @param  string $cache_dir directory to store cached templates in
     *
     * @return Smarty current Smarty instance for chaining
     */
    public function setCacheDir($cache_dir)
    {
        $this->_normalizeDir('cache_dir', $cache_dir);
        $this->_cacheDirNormalized = true;
        return $this;
    }

    /**
     * Get cache directory
     *
     * @return string path of cache directory
     */
    public function getCacheDir()
    {
        if (!$this->_cacheDirNormalized) {
            $this->_normalizeDir('cache_dir', $this->cache_dir);
            $this->_cacheDirNormalized = true;
        }
        return $this->cache_dir;
    }

    /**
     * Normalize and set directory string
     *
     * @param string $dirName cache_dir or compile_dir
     * @param string $dir     filepath of folder
     */
    private function _normalizeDir($dirName, $dir)
    {
        $this->{$dirName} = $this->_realpath(rtrim($dir, "/\\") . DS, true);
        if (!isset(Smarty::$_muted_directories[ $this->{$dirName} ])) {
            Smarty::$_muted_directories[ $this->{$dirName} ] = null;
        }
    }

    /**
     * Normalize template_dir or config_dir
     *
     * @param bool $isConfig true for config_dir
     *
     */
    private function _nomalizeTemplateConfig($isConfig)
    {
        if ($isConfig) {
            $processed = &$this->_processedConfigDir;
            $dir = &$this->config_dir;
        } else {
            $processed = &$this->_processedTemplateDir;
            $dir = &$this->template_dir;
        }
        if (!is_array($dir)) {
            $dir = (array) $dir;
        }
        foreach ($dir as $k => $v) {
            if (!isset($processed[ $k ])) {
                $dir[ $k ] = $v = $this->_realpath(rtrim($v, "/\\") . DS, true);
                $processed[ $k ] = true;
            }
        }
        $isConfig ? $this->_configDirNormalized = true : $this->_templateDirNormalized = true;
        $isConfig ? $this->_joined_config_dir = join('#', $this->config_dir) :
            $this->_joined_template_dir = join('#', $this->template_dir);
    }

    /**
     * creates a template object
     *
     * @param  string  $template   the resource handle of the template file
     * @param  mixed   $cache_id   cache id to be used with this template
     * @param  mixed   $compile_id compile id to be used with this template
     * @param  object  $parent     next higher level of Smarty variables
     * @param  boolean $do_clone   flag is Smarty object shall be cloned
     *
     * @return object  template object
     */
    public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
    {
        if ($cache_id !== null && (is_object($cache_id) || is_array($cache_id))) {
            $parent = $cache_id;
            $cache_id = null;
        }
        if ($parent !== null && is_array($parent)) {
            $data = $parent;
            $parent = null;
        } else {
            $data = null;
        }
        $_templateId = $this->_getTemplateId($template, $cache_id, $compile_id);
        $tpl = null;
        if ($this->caching && isset($this->_cache[ 'isCached' ][ $_templateId ])) {
            $tpl = $do_clone ? clone $this->_cache[ 'isCached' ][ $_templateId ] :
                $this->_cache[ 'isCached' ][ $_templateId ];
            $tpl->tpl_vars = $tpl->config_vars = array();
        } else if (!$do_clone && isset($this->_cache[ 'tplObjects' ][ $_templateId ])) {
            $tpl = clone $this->_cache[ 'tplObjects' ][ $_templateId ];
        } else {
            /* @var Smarty_Internal_Template $tpl */
            $tpl = new $this->template_class($template, $this, null, $cache_id, $compile_id, null, null);
            $tpl->templateId = $_templateId;
        }
        if ($do_clone) {
            $tpl->smarty = clone $tpl->smarty;
        }
        $tpl->parent = $parent ? $parent : $this;
        // fill data if present
        if (!empty($data) && is_array($data)) {
            // set up variable values
            foreach ($data as $_key => $_val) {
                $tpl->tpl_vars[ $_key ] = new Smarty_Variable($_val);
            }
        }
        if ($this->debugging || $this->debugging_ctrl == 'URL') {
            $tpl->smarty->_debug = new Smarty_Internal_Debug();
            // check URL debugging control
            if (!$this->debugging && $this->debugging_ctrl == 'URL') {
                $tpl->smarty->_debug->debugUrl($tpl->smarty);
            }
        }
        return $tpl;
    }

    /**
     * Takes unknown classes and loads plugin files for them
     * class name format: Smarty_PluginType_PluginName
     * plugin filename format: plugintype.pluginname.php
     *
     * @param  string $plugin_name class plugin name to load
     * @param  bool   $check       check if already loaded
     *
     * @throws SmartyException
     * @return string |boolean filepath of loaded file or false
     */
    public function loadPlugin($plugin_name, $check = true)
    {
        return $this->ext->loadPlugin->loadPlugin($this, $plugin_name, $check);
    }

    /**
     * Get unique template id
     *
     * @param string                    $template_name
     * @param null|mixed                $cache_id
     * @param null|mixed                $compile_id
     * @param null                      $caching
     * @param \Smarty_Internal_Template $template
     *
     * @return string
     */
    public function _getTemplateId($template_name, $cache_id = null, $compile_id = null, $caching = null,
                                   Smarty_Internal_Template $template = null)
    {
        $template_name = (strpos($template_name, ':') === false) ? "{$this->default_resource_type}:{$template_name}" :
            $template_name;
        $cache_id = $cache_id === null ? $this->cache_id : $cache_id;
        $compile_id = $compile_id === null ? $this->compile_id : $compile_id;
        $caching = (int) ($caching === null ? $this->caching : $caching);

        if ((isset($template) && strpos($template_name, ':.') !== false) || $this->allow_ambiguous_resources) {
            $_templateId =
                Smarty_Resource::getUniqueTemplateName((isset($template) ? $template : $this), $template_name) .
                "#{$cache_id}#{$compile_id}#{$caching}";
        } else {
            $_templateId = $this->_joined_template_dir . "#{$template_name}#{$cache_id}#{$compile_id}#{$caching}";
        }
        if (isset($_templateId[ 150 ])) {
            $_templateId = sha1($_templateId);
        }
        return $_templateId;
    }

    /**
     * Normalize path
     *  - remove /./ and /../
     *  - make it absolute if required
     *
     * @param string $path      file path
     * @param bool   $realpath  if true - convert to absolute
     *                          false - convert to relative
     *                          null - keep as it is but remove /./ /../
     *
     * @return string
     */
    public function _realpath($path, $realpath = null)
    {
        $nds = DS == '/' ? '\\' : '/';
        // normalize DS 
        $path = str_replace($nds, DS, $path);
        preg_match('%^(?<root>(?:[[:alpha:]]:[\\\\]|/|[\\\\]{2}[[:alpha:]]+|[[:print:]]{2,}:[/]{2}|[\\\\])?)(?<path>(?:[[:print:]]*))$%',
                   $path, $parts);
        $path = $parts[ 'path' ];
        if ($parts[ 'root' ] == '\\') {
            $parts[ 'root' ] = substr(getcwd(), 0, 2) . $parts[ 'root' ];
        } else {
            if ($realpath !== null && !$parts[ 'root' ]) {
                $path = getcwd() . DS . $path;
            }
        }
        // remove noop 'DS DS' and 'DS.DS' patterns
        $path = preg_replace('#([\\\\/]([.]?[\\\\/])+)#', DS, $path);
        // resolve '..DS' pattern, smallest first
        if (strpos($path, '..' . DS) != false &&
            preg_match_all('#(([.]?[\\\\/])*([.][.])[\\\\/]([.]?[\\\\/])*)+#', $path, $match)
        ) {
            $counts = array();
            foreach ($match[ 0 ] as $m) {
                $counts[] = (int) ((strlen($m) - 1) / 3);
            }
            sort($counts);
            foreach ($counts as $count) {
                $path = preg_replace('#(([\\\\/]([.]?[\\\\/])*[^\\\\/.]+){' . $count .
                                     '}[\\\\/]([.]?[\\\\/])*([.][.][\\\\/]([.]?[\\\\/])*){' . $count . '})(?=[^.])#',
                                     DS, $path);
            }
        }

        return $parts[ 'root' ] . $path;
    }

    /**
     * Empty template objects cache
     */
    public function _clearTemplateCache()
    {
        $this->_cache[ 'isCached' ] = array();
        $this->_cache[ 'tplObjects' ] = array();
    }

    /**
     * @param boolean $compile_check
     */
    public function setCompileCheck($compile_check)
    {
        $this->compile_check = $compile_check;
    }

    /**
     * @param boolean $use_sub_dirs
     */
    public function setUseSubDirs($use_sub_dirs)
    {
        $this->use_sub_dirs = $use_sub_dirs;
    }

    /**
     * @param int $error_reporting
     */
    public function setErrorReporting($error_reporting)
    {
        $this->error_reporting = $error_reporting;
    }

    /**
     * @param boolean $escape_html
     */
    public function setEscapeHtml($escape_html)
    {
        $this->escape_html = $escape_html;
    }

    /**
     * @param boolean $auto_literal
     */
    public function setAutoLiteral($auto_literal)
    {
        $this->auto_literal = $auto_literal;
    }

    /**
     * @param boolean $force_compile
     */
    public function setForceCompile($force_compile)
    {
        $this->force_compile = $force_compile;
    }

    /**
     * @param boolean $merge_compiled_includes
     */
    public function setMergeCompiledIncludes($merge_compiled_includes)
    {
        $this->merge_compiled_includes = $merge_compiled_includes;
    }

    /**
     * @param string $left_delimiter
     */
    public function setLeftDelimiter($left_delimiter)
    {
        $this->left_delimiter = $left_delimiter;
    }

    /**
     * @param string $right_delimiter
     */
    public function setRightDelimiter($right_delimiter)
    {
        $this->right_delimiter = $right_delimiter;
    }

    /**
     * @param boolean $debugging
     */
    public function setDebugging($debugging)
    {
        $this->debugging = $debugging;
    }

    /**
     * @param boolean $config_overwrite
     */
    public function setConfigOverwrite($config_overwrite)
    {
        $this->config_overwrite = $config_overwrite;
    }

    /**
     * @param boolean $config_booleanize
     */
    public function setConfigBooleanize($config_booleanize)
    {
        $this->config_booleanize = $config_booleanize;
    }

    /**
     * @param boolean $config_read_hidden
     */
    public function setConfigReadHidden($config_read_hidden)
    {
        $this->config_read_hidden = $config_read_hidden;
    }

    /**
     * @param boolean $compile_locking
     */
    public function setCompileLocking($compile_locking)
    {
        $this->compile_locking = $compile_locking;
    }

    /**
     * @param string $default_resource_type
     */
    public function setDefaultResourceType($default_resource_type)
    {
        $this->default_resource_type = $default_resource_type;
    }

    /**
     * @param string $caching_type
     */
    public function setCachingType($caching_type)
    {
        $this->caching_type = $caching_type;
    }

    /**
     * Test install
     *
     * @param null $errors
     */
    public function testInstall(&$errors = null)
    {
        Smarty_Internal_TestInstall::testInstall($this, $errors);
    }

    /**
     * <<magic>> Generic getter.
     * Calls the appropriate getter function.
     * Issues an E_USER_NOTICE if no valid getter is found.
     *
     * @param  string $name property name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->accessMap[ $name ])) {
            $method = 'get' . $this->accessMap[ $name ];
            return $this->{$method}();
        } elseif (isset($this->_cache[ $name ])) {
            return $this->_cache[ $name ];
        } elseif (in_array($name, $this->obsoleteProperties)) {
            return null;
        } else {
            trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
        }
        return null;
    }

    /**
     * <<magic>> Generic setter.
     * Calls the appropriate setter function.
     * Issues an E_USER_NOTICE if no valid setter is found.
     *
     * @param string $name  property name
     * @param mixed  $value parameter passed to setter
     */
    public function __set($name, $value)
    {
        if (isset($this->accessMap[ $name ])) {
            $method = 'set' . $this->accessMap[ $name ];
            $this->{$method}($value);
        } elseif (in_array($name, $this->obsoleteProperties)) {
            return;
        } else {
            if (is_object($value) && method_exists($value, $name)) {
                $this->$name = $value;
            } else {
                trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
            }
        }
    }

    /**
     * Error Handler to mute expected messages
     *
     * @link http://php.net/set_error_handler
     *
     * @param  integer $errno Error level
     * @param          $errstr
     * @param          $errfile
     * @param          $errline
     * @param          $errcontext
     *
     * @return bool|void
     */
    public static function mutingErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $_is_muted_directory = false;

        // add the SMARTY_DIR to the list of muted directories
        if (!isset(Smarty::$_muted_directories[ SMARTY_DIR ])) {
            $smarty_dir = realpath(SMARTY_DIR);
            if ($smarty_dir !== false) {
                Smarty::$_muted_directories[ SMARTY_DIR ] =
                    array('file' => $smarty_dir, 'length' => strlen($smarty_dir),);
            }
        }

        // walk the muted directories and test against $errfile
        foreach (Smarty::$_muted_directories as $key => &$dir) {
            if (!$dir) {
                // resolve directory and length for speedy comparisons
                $file = realpath($key);
                if ($file === false) {
                    // this directory does not exist, remove and skip it
                    unset(Smarty::$_muted_directories[ $key ]);
                    continue;
                }
                $dir = array('file' => $file, 'length' => strlen($file),);
            }
            if (!strncmp($errfile, $dir[ 'file' ], $dir[ 'length' ])) {
                $_is_muted_directory = true;
                break;
            }
        }
        // pass to next error handler if this error did not occur inside SMARTY_DIR
        // or the error was within smarty but masked to be ignored
        if (!$_is_muted_directory || ($errno && $errno & error_reporting())) {
            if (Smarty::$_previous_error_handler) {
                return call_user_func(Smarty::$_previous_error_handler, $errno, $errstr, $errfile, $errline,
                                      $errcontext);
            } else {
                return false;
            }
        }
        return;
    }

    /**
     * Enable error handler to mute expected messages
     *
     * @return void
     */
    public static function muteExpectedErrors()
    {
        /*
            error muting is done because some people implemented custom error_handlers using
            http://php.net/set_error_handler and for some reason did not understand the following paragraph:

                It is important to remember that the standard PHP error handler is completely bypassed for the
                error types specified by error_types unless the callback function returns FALSE.
                error_reporting() settings will have no effect and your error handler will be called regardless -
                however you are still able to read the current value of error_reporting and act appropriately.
                Of particular note is that this value will be 0 if the statement that caused the error was
                prepended by the @ error-control operator.

            Smarty deliberately uses @filemtime() over file_exists() and filemtime() in some places. Reasons include
                - @filemtime() is almost twice as fast as using an additional file_exists()
                - between file_exists() and filemtime() a possible race condition is opened,
                  which does not exist using the simple @filemtime() approach.
        */
        $error_handler = array('Smarty', 'mutingErrorHandler');
        $previous = set_error_handler($error_handler);

        // avoid dead loops
        if ($previous !== $error_handler) {
            Smarty::$_previous_error_handler = $previous;
        }
    }

    /**
     * Disable error handler muting expected messages
     *
     * @return void
     */
    public static function unmuteExpectedErrors()
    {
        restore_error_handler();
    }/**
 * @Author: Administrator
 * @Date:   2016-09-07 23:53:08
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-09 23:48:26
 */
class SmartyView {
	private static $smarty = NULL;

	public function __construct(){
		if(!empty(self::$smarty)) return;
		$smarty = new Smarty();

		$smarty->template_dir = APP_TPL_PATH . '/' .CONTROLLER .'/';
		$smarty->compile_dir = APP_COMPILE_PATH;
		$smarty->cache_dir = APP_CACHE_PATH;

		$smarty->left_delimiter = C('LEFT_DELIMITER');
		$smarty->right_delimiter = C('RIGHT_DELIMITER');
		$smarty->caching = C('CACHE_ON');
		$smarty->cache_lifetime =C('CACHE_TIME');

		  // p($smarty);

		self::$smarty = $smarty;
	}

	protected function display($tpl){
		// echo 111;
		self::$smarty -> display($tpl);
	}

	protected function assign($name,$value){
		// echo 111;
		self::$smarty->assign($name,$value);
	}

	protected function isCached($tpl=NULL){
		if(!C('SMARTY_ON')) E('请先开启Smarty');
		$tpl = $this->get_tpl($tpl);
		return self::$smarty->isCached($tpl);
	}p /**
 * @Author: Administrator
 * @Date:   2016-07-24 16:59:31
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-09 23:41:21
 */
//父类controller
class Controller extends SmartyView{
	private $var = array();

	public function __construct(){
		//父类构造器
		if(C('SMARTY_ON')){
			parent::__construct();
		}
		if(method_exists($this, '__init'))
			$this->__init();
		if(method_exists($this, '__auto'))
			$this->__auto();
	}
	/**
	 * [success 成功跳转页面]
	 * @param  [type]  $msg  [错误信息]
	 * @param  [type]  $url  [跳转url]
	 * @param  integer $time [提示时间]
	 * @return [type]        [description]
	 */
	protected function success($msg,$url=NULL,$time=3){
		$url=$url ? "window.location.href='". $url . "'" : "window.history.back(-1)";
		include APP_TPL_PATH . '/success.html';
	}
	/**
	 * [error 失败跳转页面]
	 * @param  [type]  $msg  [错误信息]
	 * @param  [type]  $url  [跳转url]
	 * @param  integer $time [提示时间]
	 * @return [type]        [description]
	 */
	protected function error($msg,$url=NULL,$time=3){
		$url=$url ? "window.location.href='". $url . "'" : 'window.history.back(-1)';
		echo $url;
		include APP_TPL_PATH . '/error.html';
	}

	// protected function display($tpl=NULL)
	// {
	// 	if(is_null($tpl)){
	// 		$path = APP_TPL_PATH .'/' . CONTROLLER .'/' .ACTION.'.html';
	// 		// echo $path;
	// 	}else{
	// 		$suffix = strrchr($tpl, '.');
	// 		$tpl = empty($suffix) ? $tpl . '.html' : $tpl;
	// 		$path = APP_TPL_PATH .'/'.CONTROLLER .'/'.$tpl;
	// 	}

	// 	if(!is_file($path)) E($path.'模板不存在');
	// 	extract($this->var);
	// 	include $path;
	// }
	// 就是插件，这样你就可以给OT增加很多功能，要想增加功能就得修改源程序啊，但是如果cms升级了，你的增加功能就被覆盖了，于是就出现了钩子，这样你就可以在模板里某个位置加入钩子标签，因为模版不怕被修改。大概就这么个意思了
	// 
	protected function get_tpl($tpl){
		if(is_null($tpl)){
			$path = APP_TPL_PATH . '/' .CONTROLLER .'/' .ACTION .'.html';
		}else{
			$suffix = strchr($tpl,'.');
			$tpl = empty($suffix) ? $tpl .'.html' : $tpl;;
			$path = APP_TPL_PATH . '/' . CONTROLLER . '/' .$tpl;
		}
		return $path;
	}

	protected function display($tpl=NULL){
		$path = $this->get_tpl($tpl);
		if(!file_exists($path)) E($path.'模板不存在');

		if(C('SMARTY_ON')){
			parent::display($path);
		}else{
			extract($this->var);
			include $path;
		}
	}

	protected function assign($name,$value){

		if (C('SMARTY_ON')) {
			parent::assign($name,$value);
		}else{
			if(is_array($name)){
				$this->var = array_merge($this->var , $name);
			}
			$this->var[$name] = $value; 
		}
	}
}/**
 * @Author: Administrator
 * @Date:   2016-07-23 10:47:19
 * @Last Modified by:   Administrator
 * @Last Modified time: 2016-09-09 23:11:05
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
					require_once COMMON_LIB_PATH.'/'. $af;
					// require_once $autoPath;
				}
			}
		}
	}