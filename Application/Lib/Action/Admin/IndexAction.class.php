<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends PublicAction {
	function _initialize() {
		parent::_initialize ();
	}
	public function index() {
		$this->display ();
	}
	public function setting() {
		$result = R ( "Api/Api/setting", array (
				$_POST ["name"],
				$_POST ["notification"] 
		) );
		$this->success ( "修改成功");
	}
	public function set() {
		if ($_SESSION ["wadmin"]) {
			$result = R ( "Api/Api/getsetting" );
			$this->assign ( "info", $result );
			
			$themedir = getDir("./Application/Tpl/App");
			
			for ($i = 0; $i < count($themedir); $i++) {
				$theme[$i] = simplexml_load_file("./Application/Tpl/App".$themedir[$i]."/config.xml");
				if (isset($theme[$i])) {
					$theme[$i]->dir = $themedir[$i];
				}
			}
			$this->assign("theme",$theme);
			$this->assign("settheme",$result["theme"]);
			$payresult = R ( "Api/Api/getalipay" );
			$this->assign ( "alipay", $payresult );
			$this->display ();
		}
	}
	public function settheme(){
		$name = $_GET["name"];
		$data = array("id"=>1,"theme"=>$name);
		$result = M("Info")->save($data);
		$this->success("操作成功");
	}
	public function setalipay(){
		$result = R ( "Api/Api/setalipay", array (
				$_POST ["alipayname"],
				$_POST ["partner"],
				$_POST ["key"],
                $_POST ["wxpay"]
		) );
		
		$this->success ( "操作成功" );
	}
}