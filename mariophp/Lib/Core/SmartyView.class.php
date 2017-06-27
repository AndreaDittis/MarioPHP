<?php
/**
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
	}
}