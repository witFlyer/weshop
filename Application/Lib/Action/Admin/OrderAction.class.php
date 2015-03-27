<?php
class OrderAction extends PublicAction {
	function _initialize() {
		parent::_initialize ();
	}
	public function index() {
		import ( 'ORG.Util.Page' );
		$m = D ( "Order" );
		
		$count = $m->count (); // 查询满足要求的总记录数
		$Page = new Page ( $count, 10 ); // 实例化分页类 传入总记录数和每页显示的记录数
		$Page -> setConfig('header', '条记录');
        $Page -> setConfig('theme', '<li><a>%totalRow% %header%</a></li> <li>%upPage%</li> <li>%downPage%</li> <li>%first%</li>  <li>%prePage%</li>  <li>%linkPage%</li>  <li>%nextPage%</li> <li>%end%</li> ');//(对thinkphp自带分页的格式进行自定义)
		$show = $Page->show (); // 分页显示输出
		
		$result = $m->limit ( $Page->firstRow . ',' . $Page->listRows )->order("id desc")->relation(true)->select ();
		$this->assign ( "result", $result );
		$this->assign ( "page", $show ); // 赋值分页输出
		$this->display ();
	}
	public function del(){
		$result = R ( "Api/Api/delorder", array (
				$_GET ['id'],
		) );
		$this->success ( "操作成功" );
	}
	public function publish(){
		$result = R ( "Api/Api/publish", array (
				$_GET ['id'],
		) );
		$this->success ( "操作成功" );
	}
    public function wxprint(){
        $id = $_GET["id"];
        $msg = '';
        $result = D("Order")->where(array("id"=>$id))->relation(true)->find();

        if ($result["pay_status"] == 0) {
            $pay_status = "未付款";
        }else{
            $pay_status = "已付款";
        }

        $msgtitle  = '欢迎您订购

订单编号：'.$result["orderid"].'

条目      单价（元）    数量
--------------------------------------------
';
        $detail = '';
        for ($j=0; $j < count($result["cartdata"]); $j++) {
            $row = $result["cartdata"][$j];
            $title = $row['name'];
            $price = $row['price'];
            $num = $row['num'];

            $detail .=
                $title.'      '.$price.'      '.$num.'
';
        }
        $msgcontent = $detail;

        $msgfooter = '
备注：'.$result["note"].'
--------------------------------------------
合计：'.$result["totalprice"].'元
付款状态：'.$pay_status.'

联系用户：'.$result["contact"]["name"] .'
送货地址：'.$result["contact"]["city"].$result["contact"]["area"].$result["contact"]["address"].'
联系电话：'.$result["contact"]["phone"].'
订购时间：'.$result["time"].'




';//自由输出

        $msg .= $msgtitle . $msgcontent . $msgfooter;


        // print_r($msg);
        // return;
        $apiKey       = "";//apiKey
        $mKey         = "";//秘钥
        $partner      = "";//用户id
        $machine_code = "";//机器码
        import('wxPrint', APP_PATH . 'Common', '.php');
        $params = array(
            'partner'=>$partner,
            'machine_code'=>$machine_code,
            'content'=>$msg,
        );
        $sign = generateSign($params,$apiKey,$mKey);

        $params['sign'] = $sign;

        echo httppost1($params);
        $this->success ( "操作成功" );
    }
	public function payComplete(){
		$result = R ( "Api/Api/payComplete", array (
				$_GET ['id'],
		) );
		$this->success ( "操作成功" );
	}
}