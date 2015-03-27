<?php
class WechatAction extends Action {
	public function init() {
		import ( 'Wechat', APP_PATH . 'Common/Wechat', '.class.php' );
		$config = M ( "Wxconfig" )->where ( array (
				"id" => "1" 
		) )->find ();
		
		$options = array (
				'token' => $config ["token"], // 填写你设定的key
				'encodingaeskey' => $config ["encodingaeskey"], // 填写加密用的EncodingAESKey
				'appid' => $config ["appid"], // 填写高级调用功能的app id
				'appsecret' => $config ["appsecret"], // 填写高级调用功能的密钥
				'partnerid' => $config ["partnerid"], // 财付通商户身份标识
				'partnerkey' => $config ["partnerkey"], // 财付通商户权限密钥Key
				'paysignkey' => $config ["paysignkey"]  // 商户签名密钥Key
				);
		$weObj = new Wechat ( $options );
		return $weObj;
	}
	public function index() {
		$weObj = $this->init ();
		$weObj->valid ();
		$type = $weObj->getRev ()->getRevType ();
		switch ($type) {
			case Wechat::MSGTYPE_TEXT :
// 				$weObj->text ( "hello, I'm wechat" )->reply ();
				$key = $weObj->getRev()->getRevContent();
				
				$replay = M("Wxmessage")->where(array("key"=>$key))->select();
				for ($i = 0; $i < count($replay); $i++) {
					if ($replay[$i]["type"]==0) {
						$appUrl = 'http://' . $this->_server ( 'HTTP_HOST' ) . __ROOT__;
						$newsArr[$i] = array(
								'Title' => $replay[$i]["title"],
								'Description' => $replay[$i]["description"],
								'PicUrl' => $appUrl . '/Public/Uploads/'.$replay[$i]["picurl"],
								'Url' => $replay[$i]["url"].'&uid=' . $weObj->getRevFrom ()
						);
					}else{
						$weObj->text ( $replay[$i]["title"] )->reply ();
						exit ();
					}
				}
				$weObj->getRev ()->news ( $newsArr )->reply ();
				exit ();
				break;
			case Wechat::MSGTYPE_EVENT :
				$eventype = $weObj->getRev ()->getRevEvent ();
				if ($eventype ['event'] == "CLICK") {
					$appUrl = 'http://' . $this->_server ( 'HTTP_HOST' ) . __ROOT__;
					
					$news = M ( "Wxmessage" )->where ( array (
							"key" => $eventype ['key'],
							"type" => 0 
					) )->select ();
					
					if ($news) {
						for($i = 0; $i < count ( $news ); $i ++) {
							$newsArr[$i] = array(
								'Title' => $news[$i]["title"],
								'Description' => $news[$i]["description"],
								'PicUrl' => $appUrl . '/Public/Uploads/'.$news[$i]["picurl"],
								'Url' => $news[$i]["url"].'&uid=' . $weObj->getRevFrom ()
							);
						}

						$weObj->getRev ()->news ( $newsArr )->reply ();
					}
					
				}elseif ($eventype['event'] == "subscribe") {
    				$weObj->text ( "欢迎您关注wemall商城！" )->reply ();
				}
				exit ();
				break;
			default :
				$weObj->text ( "help info" )->reply ();
		}
	}
	public function createMenu() {
		$menu = M ( "Wxmenu" )->order ( "listorder asc" )->select ();
		for($i = 0; $i < count ( $menu ); $i ++) {
			if ($menu [$i] ["menu_type"] == "view") {
				$menu [$i] ["type"] = $menu [$i] ["menu_type"];
				$menu [$i] ["name"] = $menu [$i] ["menu_name"];
				$menu [$i] ["url"] = $menu [$i] ["view_url"];
			} else {
				$menu [$i] ["type"] = $menu [$i] ["menu_type"];
				$menu [$i] ["name"] = $menu [$i] ["menu_name"];
				$menu [$i] ["key"] = $menu [$i] ["event_key"];
			}
			unset ( $menu [$i] ["menu_id"] );
			unset ( $menu [$i] ["pid"] );
			unset ( $menu [$i] ["listorder"] );
			unset ( $menu [$i] ["status"] );
			unset ( $menu [$i] ["menu_type"] );
			unset ( $menu [$i] ["menu_name"] );
			unset ( $menu [$i] ["event_key"] );
			unset ( $menu [$i] ["view_url"] );
		}
		$newmenu ["button"] = $menu;
		
		$weObj = $this->init ();
		$weObj->createMenu ( $newmenu );
		$this->success ( "重新创建菜单成功!" );
	}
}