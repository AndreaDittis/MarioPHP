 <?php /**
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
}
