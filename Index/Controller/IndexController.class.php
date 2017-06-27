<?php
class IndexController extends Controller{
	public function __init(){
		// echo "ok2";
	}

	public function __empty(){
		echo 'Empty Method';
	}
	public function index(){
		
		// $model = M('test')->all();
	
		// $result = $model -> find();
		 // p($model);
		// $this->success("OK");
		// Log::write('你好');
		// go('http://www.baidu.com',3,'yi hui jiu yao tiao zou la');
		// E('hello');
		// $var = '111';
		// $this->assign('var',$var);
		// p(IS_POST);
		 // print_const();
		// d(111);
		// echo __PUBLIC__;
		// $code = new Code();
		// $code->show();
		// include 'i.html';
		// if(IS_POST){
		// 	$data = array('name' => 'zhangguorong');
		// 	$m = M('test')->where('id=0')->update($data);
		// 	if($m){
		// 		$this->success('更新成','',3);
		// 	}
		// }
		// $all=$m=M('test')->all();
		// p($all);
		if(!$this->isCached()){
			$this->assign('var',time());
		}
		// $this->assign('var','1111');
		 $this->display();
		 
		// // print_const()
		// $model =K('test');
		// $model->get_all();

	}
}
?>