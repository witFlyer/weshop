<?php
class GoodAction extends PublicAction {
	function _initialize() {
		parent::_initialize ();
	}
	public function index() {
		import ( 'ORG.Util.Page' );
		$m = M ( "Good" );
		
		$count = $m->count (); // 查询满足要求的总记录数
		$Page = new Page ( $count, 12 ); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page -> setConfig('header', '条记录');
        $Page -> setConfig('theme', '<li><a>%totalRow% %header%</a></li> <li>%upPage%</li> <li>%downPage%</li> <li>%first%</li>  <li>%prePage%</li>  <li>%linkPage%</li>  <li>%nextPage%</li> <li>%end%</li> ');//(对thinkphp自带分页的格式进行自定义)
		$show = $Page->show (); // 分页显示输出
		
		$result = $m->limit ( $Page->firstRow . ',' . $Page->listRows )->select ();
		
		for($i = 0; $i < count ( $result ); $i ++) {
			$menu_id = $result [$i] ["menu_id"];
			$result [$i] ["menu"] = R ( "Api/Api/getmenuvalue", array (
					$menu_id 
			) );
		}
		$menu = R ( "Api/Api/getarraymenu" );
		
		$this->assign ( "menu", $menu );
		$this->assign ( "addmenu", $menu );
		$this->assign ( "page", $show ); // 赋值分页输出
		$this->assign ( "result", $result );
		$this->display ();
	}
	public function addgood() {
		if ($_POST["goodid"]) {
			$data ["id"] = $_POST["goodid"];
			$data ["menu_id"] = $_POST ["addmenuid"];
			$data ["name"] = $_POST ["addname"];
			$data ["price"] = $_POST ["addprice"];
			$data ["old_price"] = $_POST ["add_old_price"];
			$data ["sort"] = $_POST ["addsort"];
			if ($_FILES ['addimage'] ['name'] !== '') {
				$img = $this->upload ();
				$picurl = $img [0] [savename];
				$data ["image"] = $picurl;
			}
			$data ["status"] = $_POST ["addstatus"];
			$data ["detail"] = $_POST ["editorValue"];
			
			$result = R ( "Api/Api/addgood", array($data) );
			$this->success ( "修改商品成功！" );
		}else{
			$data ["menu_id"] = $_POST ["addmenuid"];
			$data ["name"] = $_POST ["addname"];
			$data ["price"] = $_POST ["addprice"];
			$data ["old_price"] = $_POST ["add_old_price"];
			$data ["sort"] = $_POST ["addsort"];
			if ($_FILES ['addimage'] ['name'] !== '') {
				$img = $this->upload ();
				$picurl = $img [0] [savename];
				$data ["image"] = $picurl;
			} else {
				$this->error ( "未上传图片！" );
			}
			$data ["status"] = $_POST ["addstatus"];
			$data ["detail"] = $_POST ["editorValue"];
			
			$result = R ( "Api/Api/addgood", array($data) );
			$this->success ( "添加商品成功！" );
		}
	}
	public function delgood() {
		$result = R ( "Api/Api/delgood", array (
				$_GET ["id"] 
		) );
		$this->success ( "删除商品成功！" );
	}
	public function getgoodid() {
		$id = $_POST ["id"];
		$result = M ( "Good" )->where ( array (
				"id" => $id 
		) )->find ();
		if ($result) {
			$this->ajaxReturn ( $result );
		}
	}
}