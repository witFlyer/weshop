<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {
	public function index() {
		if ($_GET ['uid']) {
			$info = R ( "Api/Api/gettheme" );
			C ( "DEFAULT_THEME", $info ["theme"] );
			$this->assign ( "info", $info );
			
			$menuresult = R ( "Api/Api/getmenu" );
			$this->assign ( "menu", $menuresult );
			
			$goodsresult = R ( "Api/Api/getgood" );
			$this->assign ( "goods", $goodsresult );
			
			$uid = $_GET ["uid"];
			$usersresult = R ( "Api/Api/getuser", array (
					$uid 
			) );
			
			$alipay = M ( "Alipay" )->find ();
			if ($alipay) {
				$this->assign ( "alipay", 1 );
                $this->assign ( "wxpay", 1 );
			}
			
			$this->assign ( "users", $usersresult );
			$this->display ();
		} else {
			echo '请使用微信访问!';
		}
	}
	public function fetchgooddetail() {
		$where ["id"] = $_POST ["id"];
		$result = M ( "Good" )->where ( $where )->find ();
		if ($result) {
			$this->ajaxReturn ( $result );
		}
	}
	public function getorders() {
		$uid = $_POST ['uid'];
		$user_id = M ( "User" )->where ( array (
				"uid" => $uid 
		) )->find ();
		$result = M ( "Order" )->where ( array (
				"user_id" => $user_id ["id"] 
		) )->order ( 'id desc' )->select ();
		$this->ajaxReturn ( $result );
	}
	public function addorder() {
		$uid = htmlspecialchars ( $_POST ['uid'] );
		
		$username = $_POST ['userData'] [0] [value];
		$phone = $_POST ['userData'] [1] [value];
		$pay = $_POST ['userData'] [2] [value];
		
		$address = $_POST ['userData'] [3] [value];
		$note = $_POST ['userData'] [4] [value];
		$totalprice = $_POST ['totalPrice'];
		$cartdata = stripslashes ( $_POST ['cartData'] );
		
		$orderid = date ( "YmdHis" ) . mt_rand ( 1, 9 );
		$time = date ( "Y/m/d H:i:s" );
		switch ($pay) {
			case 0 :
				$pay_style = "货到付款";
				break;
			case 1 :
				$pay_style = "支付宝";
				break;
		}
		
		$data ["orderid"] = $orderid;
		$data ["totalprice"] = $totalprice;
		$data ["pay_style"] = $pay_style;
		$data ["pay_status"] = "0";
		$data ["note"] = $note;
		$data ["order_status"] = '0';
		$data ["time"] = $time;
		$data ["cartdata"] = $cartdata;
		
		$userdata = M ( "User" )->where ( array (
				"uid" => $uid 
		) )->find ();
		if ($userdata) {
			$user_id = $userdata ["id"];
			$user ["id"] = $user_id;
			$user ["username"] = $username;
			$user ["phone"] = $phone;
			$user ["address"] = $address;
			M ( "User" )->save ( $user );
			
			$data ["user_id"] = $user_id;
			M ( "Order" )->add ( $data );
		} else {
			$user ["uid"] = $uid;
			$user ["username"] = $username;
			$user ["phone"] = $phone;
			$user ["address"] = $address;
			$user_id = M ( "User" )->add ( $user );
			$data ["user_id"] = $user_id;
			M ( "Order" )->add ( $data );
		}

        $result = D("Order")->where(array("id" => $orderid))->relation(true)->find();
        $return["result"] = $result;
        if ($_POST["payment"] == "1") {
            $payGood["body"] = "在线支付";
            $payGood["orderid"] = $data["orderid"];
            $payGood["totalprice"] = floatval($data["totalprice"])*100;
            $payGood["returnurl"] = 'http://' . $_SERVER ['SERVER_NAME'] . __ROOT__ .'/index.php?g=App&m=Index&a=index&uid='.$uid;
            cookie("payGood", json_encode($payGood));

            $return["pay"] = 'http://' . $_SERVER ['SERVER_NAME'] . __ROOT__ . '/api/wxPay/js_api_call.php';
        }
		
		$alipay = M ( "Alipay" )->find ();
		if ($pay == 1 && $alipay) {
			echo 'http://' . $_SERVER ['SERVER_NAME'] . __ROOT__ . '/api/wapalipay/alipayapi.php?WIDseller_email=' . $alipay ['alipayname'] . '&WIDout_trade_no=' . $orderid . '&WIDsubject=' . $orderid . '&WIDtotal_fee=' . $totalprice;
		}
	}
	
	// app start
	public function appregister() {
		$username = $_POST ["username"];
		$password = $_POST ["password"];
		$phone = $_POST ["phone"];
		
		if ($username && $password && $phone) {
			$find = M ( "User" )->where ( array (
					"phone" => $phone 
			) )->select ();
			if (! $find) {
				$data ["username"] = $username;
				$data ["phone"] = $phone;
				$data ["password"] = md5 ( $password );
				$data ["uid"] = date ( "His" ) . mt_rand ( 1, 9 );
				$data ["time"] = date ( "Y/m/d H:i:s" );
				
				$result = M ( "User" )->add ( $data );
				if ($result) {
					$this->ajaxReturn ( $result );
				}
			}
		}
	}
	public function applogin() {
		$phone = $_POST ["phone"];
		$password = md5 ( $_POST ["password"] );
		
		if ($phone && $password) {
			$result = M ( "User" )->where ( array (
					"phone" => $phone,
					"password" => $password 
			) )->find ();
			if ($result) {
				$this->ajaxReturn ( $result );
			}
		}
	}
	public function appgetgood() {
		$result = M ( "Good" )->select ();
		if ($result) {
			$this->ajaxReturn ( $result );
		}
	}
	public function appdoaddress() {
		$do = $_POST ["do"];
		$uid = $_POST ["uid"];
		
		switch ($do) {
			case 1 :
				$result = M ( "User" )->where ( array (
						"uid" => $uid 
				) )->find ();
				if ($result) {
					$this->ajaxReturn ( $result );
				}
				break;
			case 2 :
				$address = $_POST ["address"];
				$data ["address"] = $address;
				$result = M ( "User" )->where ( array (
						"uid" => $uid 
				) )->save ( $data );
				if ($result) {
					$this->ajaxReturn ( $result );
				}
				break;
			default :
				;
				break;
		}
	}
	public function appdoorder() {
		$do = $_POST ["do"];
		$uid = $_POST ["uid"];
		
		switch ($do) {
			case 1 :
				$cartdata = $_POST ["cartdata"];
				$note = $_POST ["note"];
				$cartarray = json_decode ( $cartdata, true );
				$totalprice = 0;
				for($i = 0; $i < count ( $cartarray ); $i ++) {
					unset ( $cartarray [$i] ["id"] );
					unset ( $cartarray [$i] ["image"] );
					$totalprice += $cartarray [$i] ["num"] * $cartarray [$i] ["price"];
				}
				$cartdata = json_encode ( $cartarray );
				$orderid = date ( "YmdHis" ) . mt_rand ( 1, 9 );
				$time = date ( "Y/m/d H:i:s" );
				$user = M ( "User" )->where ( array (
						"uid" => $uid 
				) )->find ();
				
				$data ["orderid"] = $orderid;
				$data ["totalprice"] = $totalprice;
				$data ["pay_style"] = "货到付款";
				$data ["pay_status"] = "0";
				$data ["note"] = $note;
				$data ["order_status"] = '0';
				$data ["time"] = $time;
				$data ["cartdata"] = $cartdata;
				$data ["user_id"] = $user ["id"];
				
				$result = M ( "Order" )->add ( $data );
				if ($result) {
					$this->ajaxReturn ( $result );
				}
				
				break;
			case 2 :
				$id = M ( "User" )->where ( array (
						"uid" => $uid 
				) )->find ();
				$id = $id ["id"];
				
				$result = M ( "Order" )->where ( array (
						"user_id" => $id 
				) )->select ();
				if ($result) {
					$this->ajaxReturn ( $result );
				}
				break;
			case 3 :
				$orderid = $_POST ["orderid"];
				$result = M ( "Order" )->where ( array (
						"orderid" => $orderid 
				) )->find ();
				
				$user = M ( "User" )->where ( array (
						"uid" => $uid 
				) )->find ();
				
				$result = array_merge ( $result, $user );
				
				if ($result) {
					$this->ajaxReturn ( $result );
				}
				
				break;
			default :
				;
				break;
		}
	}
}