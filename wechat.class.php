<?php 
/**
* wechat sdk
*/
class WeChat
{
	//消息类型
	const MSGTYPE_TEXT = 'text';
	const MSGTYPE_IMAGE = 'image';
	const MSGTYPE_MUSIC = 'music';
	const MSGTYPE_VOICE = 'voice';
	const MSGTYPE_NEWS = 'news';
	const MSGTYPE_VIDEO = 'video';
	const MSGTYPE_SHORTVIDEO = 'shortvideo';
	//事件
	const EVENT_SUBSCRIBE = 'subscribe';       //订阅
	const EVENT_UNSUBSCRIBE = 'unsubscribe';   //取消订阅
	const EVENT_SCAN = 'SCAN';                 //扫描带参数二维码
	const EVENT_LOCATION = 'LOCATION';         //上报地理位置
	const EVENT_MENU_VIEW = 'VIEW';                     //菜单 - 点击菜单跳转链接
	const EVENT_MENU_CLICK = 'CLICK';                   //菜单 - 点击菜单拉取消息
	const EVENT_MENU_SCAN_PUSH = 'scancode_push';       //菜单 - 扫码推事件(客户端跳URL)
	const EVENT_MENU_SCAN_WAITMSG = 'scancode_waitmsg'; //菜单 - 扫码推事件(客户端不跳URL)
	const EVENT_MENU_PIC_SYS = 'pic_sysphoto';          //菜单 - 弹出系统拍照发图
	const EVENT_MENU_PIC_PHOTO = 'pic_photo_or_album';  //菜单 - 弹出拍照或者相册发图
	const EVENT_MENU_PIC_WEIXIN = 'pic_weixin';         //菜单 - 弹出微信相册发图器
	const EVENT_MENU_LOCATION = 'location_select';      //菜单 - 弹出地理位置选择器

	//通用api接口前缀
	const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin/';
	//菜单管理
	const MENU_CREATE_URL = 'menu/create?';
	const MENU_GET_URL = 'menu/get?';
	const MENU_DELETE_URL = 'menu/delete?';
	//用户管理
	const TAGS_CREATE_URL = 'tags/create?';
	const TAGS_GET_URL = 'tags/get?';
	const TAGS_UPDATE_URL = 'tags/update?';
	const TAGS_DELETE_URL = 'tags/delete?';
	const TAGS_FANS_URL = 'user/tag/get?';
	const TAGS_BATCH_URL = 'tags/members/batchtagging?';
	const TAGS_CANCLE_URL = 'tags/members/batchuntagging?';
	const TAGS_LIST_URL = 'tags/getidlist?';

	const USER_INFO_URL = 'user/info?';
	const USERS_INFO_URL = 'user/info/batchget?';
	const USER_LIST_URL = 'user/get?';
	const USER_UPDATE_URL = 'user/info/updateremark?';
	const USER_BLACKLIST_URL = 'tags/members/getblacklist?';
	const USER_BLACK_URL = 'tags/members/batchblacklist?';
	const USER_UNBLACK_URL = 'tags/members/batchunblacklist?';

	//账号管理
	const QRCODE_CREATE_URL = 'qrcode/create?';
	const SHORT_URL = 'shorturl?';
	const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';

	//素材管理接口
	const MEDIA_UPLOAD_URL = 'media/upload?';	//新增临时素材
	const MEDIA_GET_URL = 'media/get?';		//获取临时素材
	const MATERIAL_ADDNEWS_URL = 'material/add_news?';		//新增永久图文素材
	const MEDIA_ADDIMG_URL = 'media/uploadimg?';		//新增永久图片素材
	const MATERIAL_ADDMATERIAL_URL = 'material/add_material?';		//新增其他类型永久素材
	const MATERIAL_GETMATERIAL_URL = 'material/get_material?';		//获取永久素材
	const MATERIAL_DELMATERIAL_URL = 'material/del_material?';		//删除永久素材
	const MATERIAL_UPDATENEWS_URL = 'material/update_news?';		//修改永久素材
	const MATERIAL_COUNT_URL = 'material/get_materialcount?';		//获取永久素材总数
	const MATERIAL_LIST_URL = 'material/batchget_material?';		//获取永久素材列表

	//获取凭证
	const GET_TOKEN_URL = 'token?';
	//获取微信服务器ip
	const SERVER_IP_URL = 'getcallbackip?';

	private $token;
	private $appid;
	private $appsecret;
	private $access_token;
	private $msg;
	private $receive;
	private $encodingAesKey;
	private $encrypt_type;
	public $errMsg = "Hello shiyanlou";
	public $errCode=-1;
	public $dubug;


	function __construct($options = [])
	{
		$this->token = isset($options['token'])?$options['token']:'';
		// $this->encodingAesKey = isset($options['encodingaeskey'])?$options['encodingaeskey']:'';
		$this->appid = isset($options['appid'])?$options['appid']:'';
		$this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
		$this->dubug = isset($options['dubug'])?$options['dubug']:false;
		$this->access_token = "vMun3RRfJaCugRZa3JY529EaLRwB15_t1-w27RcipCtt16W4SOteICjf2eHL2WbANWEGoQgw0dNbI0uHlPnM6nhTtLYoFTG04g1HxQi12D4IbmcscOAKKg7KJSsF9_xVVHLbAFAMYC";
	}

	//接入验证
	public function valid()
	{
		$echoStr = $_GET["echostr"];
		//valid signature , option
		if($this->checkSignature()){
			$this->log($echoStr,'接入验证');
			echo $echoStr;
			exit;
		}
	}

	//Signature验证
	private function checkSignature()
	{
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		$tmpArr = array($this->token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	public function getToken()
	{
		if (!isset($this->access_token)) {
			$this->getAccessToken();
		}
		return $this->access_token;
	}

	//获取AccessToken
	public function getAccessToken($appid='',$appsecret='')
	{
		if (!$appid || !$appsecret) {
			$appid = $this->appid;
			$appsecret = $this->appsecret;
		}
		$url = self::API_URL_PREFIX . self::GET_TOKEN_URL . 'grant_type=client_credential&appid=' . $appid . '&secret=' . $appsecret;
		$result = $this->http_get($url);
		if ($result) {
			$this->log($result,'获取 access_token');
			$result = json_decode($result);
			$this->access_token = $result->access_token;
			return true;
		} else {
			return false;
		}
	}

	//设置文本信息
	public function text($text = '')
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_TEXT,
			'Content'=>$this->textFilter($text),
		];
		$this->message($msg);
		return $this;
	}

	//回复图片消息
	public function image($info)
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_IMAGE,
			'Image'=>['MediaId'=>$info['mediaid']]
		];
		$this->message($msg);
		return $this;
	}

	//回复语音消息
	public function voice($info)
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_VOICE,
			'Voice'=>['MediaId'=>$info['mediaid']]
		];
		$this->message($msg);
		return $this;
	}

	//回复视频消息
	public function video($info)
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_VIDEO,
			'Video'=>['MediaId'=>$info['mediaid'],'Title'=>$info['title'],'Description'=>$info['description']]
		];
		$this->message($msg);
		return $this;
	}

	//回复音乐消息
	public function music($info)
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_MUSIC,
			'Music'=>[
				'Title'=>$info['title'],
				'Description'=>$info['desc'],
				'MusicUrl'=>$info['url'],
				'HQMusicUrl'=>$info['hqurl'],
				'ThumbMediaId'=>$info['thumbid'],
			]
		];
		$this->message($msg);
		return $this;
	}

	//设置发送数据
	public function message($msg = [],$append = false)
	{
		if (empty($msg)) {
			$this->msg = $msg;
		} elseif (is_array($msg) && !empty($msg)) {
			if ($append) {
				$this->msg = array_merge($this->msg,$msg);
			} else {
				$this->msg = $msg;
			}
			return $this->msg;
		}
		return $this->msg;
	}

	//回复消息
	public function reply($msg = [])
	{
		if (empty($msg)) {
			if (empty($this->msg)) {
				return false;
			} 
		} else {
			$this->msg = $msg;
		}
		$xmlData = $this->xml_encode($this->msg);
		if ($this->dubug) {
			$this->log($xmlData,'回复');
		}
		echo $xmlData;
	}

	//xml格式编码
	public function xml_encode($data,$root = 'xml',$attr = '',$encoding='utf-8')
	{
		if (is_array($attr)) {
			$attr1 = [];
			foreach ($attr as $key => $value) {
				$attr1[] = "{$key}=\"{$value}\"";
			}
			$attr = implode(' ',$attr1);
		}
		$xml = '';
		$attr = empty($attr) ? '' : trim($attr);
		$xml .= "<{$root}{$attr}>";
		$xml .= self::dataToXml($data);
		$xml .= "</{$root}>";
		return $xml;
	}

	//将数组转为xml
	public static function dataToXml($data)
	{
		$xml = '';
		foreach ($data as $key => $value) {
			is_numeric($key) && $key = "item id=\"$key\"";
			$xml .= "<$key>";
			$xml .= (is_array($value) || is_object($value)) ? self::dataToXml($value) : self::safeXmlStr($value);
			$xml .= "</$key>";
		}
		return $xml;
	}

	//去掉控制字符
	public static function safeXmlStr($str)
	{
		return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
	}


	/**
	 * 过滤文字回复\r\n换行符
	 * @param string $text
	 * @return string|mixed
	 */
	private function textFilter($text) {
		return str_replace("\r\n", "\n", $text);
	}

	//日志信息
	public function log($data,$option)
	{
		$filename = './log.txt';
		$logfile = fopen('./log.txt','a+')  or die("Unable to open file!");
		if (is_array($data)) {
			$data = json_encode($data);
		}
		$logdata = '时间：'.date('Y-n-d H:m:s')."\r\n".'操作：'.$option."\r\n".'内容：'.$data."\r\n ============================= \r\n";
		fwrite($logfile,$logdata);
		fclose($logfile);
		return true;
	}

	//获取微信服务器的ip地址
	public function getServerIp()
	{
		$url = self::API_URL_PREFIX . self::SERVER_IP_URL . 'access_token='.$this->access_token;
		$result = $this->http_get($url);
		if ($result) {
			$result = json_decode($result);
		  }
		return $result->ip_list;
	}

	//获取接收到的信息
	public function getRec()
	{
		if ($this->receive) {
			if ($this->dubug){
				$this->log($postStr,'接收');
			}
			return $this;
		}
		$postStr = file_get_contents("php://input");
		if ($this->dubug){
			$this->log($postStr,'接收');
		}
		if (!empty($postStr)) {
			$this->receive = (array)simplexml_load_string($postStr,'SimpleXMLElement', LIBXML_NOCDATA);
		}
		return $this;
	}

	//获取接受数据
	public function getReceiveDate()
	{
		return $this->receive;
	}

	//信息来自
	public function getRecFrom()
	{
		if (isset($this->receive['FromUserName'])) {
			return $this->receive['FromUserName'];
		} else {
			return false;
		}
	}

	//信息送至
	public function getRecTo()
	{
		if (isset($this->receive['ToUserName'])) {
			return $this->receive['ToUserName'];
		} else {
			return false;
		}
	}

	//获取消息类型
	public function getRecType()
	{
		if (isset($this->receive['MsgType'])) {
			return $this->receive['MsgType'];
		} else {
			return false;
		}
	}

	//获取msgid
	public function getRecId()
	{
		if (isset($this->receive['MsgId'])) {
			return $this->receive['MsgId'];
		} else {
			return false;
		}
	}

	//获取消息创建时间
	public function getRecTime()
	{
		if (isset($this->receive['CreateTime'])) {
			return $this->receive['CreateTime'];
		} else {
			return false;
		}
	}

	//获取消息文本信息
	public function getRecContent()
	{
		if (isset($this->receive['Content'])) {
			return $this->receive['Content'];
		} else {
			return false;
		}
	}

	//获取消息图片信息
	public function getRecPic()
	{
		if (isset($this->receive['PicUrl'])) {
			return ['mediaid'=>$this->receive['MediaId'],'picurl'=>$this->receive['PicUrl']];
		} else {
			return false;
		}
	}

	//获取消息音频信息
	public function getRecVoice()
	{
		if (isset($this->receive['MediaId'])) {
			return ['mediaid'=>$this->receive['MediaId'],'format'=>$this->receive['Format']];
		} else {
			return false;
		}
	}

	//获取消息视频信息
	public function getRecVideo()
	{
		if (isset($this->receive['MediaId'])) {
			return ['mediaid'=>$this->receive['MediaId'],'thumbMediaId'=>$this->receive['ThumbMediaId']];
		} else {
			return false;
		}
	}

	//获取事件推送
	public function getRecEvent()
	{
		if (isset($this->receive['Event'])) {
			$event['event'] = $this->receive['Event'];
		}
		if (isset($this->receive['EventKey'])) {
			$event['key'] = $this->receive['EventKey'];
		}
		if (isset($this->receive['Ticket'])) {
			$event['ticket'] = $this->receive['Ticket'];
		}
		if (isset($event) && !empty($event)) {
			return $event;
		} else {
			return false;
		}
	}

	//获取地理位置
	public function getLocation()
	{
		$locinfo['latitude'] = $this->receive['Location_X'];
		$locinfo['longitude'] = $this->receive['Location_Y'];
		$locinfo['label'] = $this->receive['Label'];
		if (!empty($locinfo)) {
			return $locinfo;
		} else {
			return false;
		}
	}

	//扫描二维码
	public function getScanInfo()
	{
		$info['type'] = $this->receive['ScanCodeInfo']->ScanType;
		$info['result'] = $this->receive['ScanCodeInfo']->ScanResult;
		if (!empty($info)) {
			return $info;
		} else {
			return false;
		}
	}
	
	//上传临时素材
	public function uploadTmp($type,$data)
	{
		$url = self::API_URL_PREFIX . self::MEDIA_UPLOAD_URL . 'access_token=' . $this->access_token . '&type=' . $type;
		$result = $this->http_post($url, $data, true);
		if ($result) {
			$json = (array)json_decode($result);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			if ($this->dubug) {
				$this->log($result,'上传临时素材');
			}
			return $json;
		} else {
			return false;
		}
	}

	//获取临时素材
	public function getTmp($mediaid)
	{
		$url = self::API_URL_PREFIX . self::MEDIA_GET_URL . 'access_token=' . $this->access_token . '&media_id=' . $mediaid;
		$result = $this->http_get($url);
		if ($result)
		{
			if (is_string($result)) {
				$json = json_decode($result,true);
				if (isset($json['errcode'])) {
					$this->errCode = $json['errcode'];
					$this->errMsg = $json['errmsg'];
					return false;
				}
			}
			if ($this->dubug) {
				$this->log($result,'获取临时素材');
			}
			return $result;
		}
		return false;
	}

	//新增永久素材(其他类型需申明，视频素材需要描述数据)
	public function addMaterial($type,$data,$is_video=false,$info=[])
	{
		switch ($type) {
			case 'news':
				$url = self::API_URL_PREFIX . self::MATERIAL_ADDNEWS_URL . 'access_token=' . $this->access_token;
				$result = $this->http_post($url, json_encode($data));
				break;
			case 'image':
				$url = self::API_URL_PREFIX . self::MEDIA_ADDIMG_URL . 'access_token=' . $this->access_token;
				$result = $this->http_post($url, $data, true);
				break;
			default:
				if ($is_video) {
					$data['description'] = json_encode($info);
				}
				$url = self::API_URL_PREFIX . self::MATERIAL_ADDMATERIAL_URL . 'access_token=' . $this->access_token . '&type=' . $type;
				$result = $this->http_post($url, $data, true);
				break;
		}
		
		if ($result) {
			$json = (array)json_decode($result);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			if ($this->dubug) {
				$this->log($result,'新增永久素材');
			}
			return $json;
		} else {
			return false;
		}
	}

	//获取永久素材
	public function getMaterial($mediaid)
	{
		$url = self::API_URL_PREFIX . self::MATERIAL_GETMATERIAL_URL . 'access_token=' . $this->access_token;
		$data['media_id'] = $mediaid;
		$result = $this->http_post($url, json_encode($data), false);
		if ($result)
		{
			if (is_string($result)) {
				$json = json_decode($result,true);
				if ($json) {
					if (isset($json['errcode'])) {
						$this->errCode = $json['errcode'];
						$this->errMsg = $json['errmsg'];
						return false;
					}
				}
			if ($this->dubug) {
				$this->log($result,'获取永久素材');
			}
			return $result;
			} else {
				return false;
			}
		}
	}

	//删除永久素材
	public function delMaterial($mediaid)
	{
		$url = self::API_URL_PREFIX . self::MATERIAL_DELMATERIAL_URL . 'access_token=' . $this->access_token;
		$data['media_id'] = $mediaid;
		$result = $this->http_post($url, json_encode($data), false);
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				if ($this->errCode == 0) {
					if ($this->dubug) {
						$this->log($result,'删除永久素材');
					}
					return true;
				} else {
					return false;
				}
			}
			return false;
		}
		return false;
	}

	//修改永久图文素材
	public function updateNews($mediaid,$index,$data)
	{
		$data['media_id'] = $mediaid;
		$data['index'] = $index;
		$url = self::API_URL_PREFIX . self::MATERIAL_UPDATENEWS_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($data));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			if ($this->dubug) {
				$this->log($result,'修改永久图文素材');
			}
			return $json;
		}
		return false;
	}

	//获取素材列表
	public function getMaterialList($type,$offset,$count)
	{
		$url = self::API_URL_PREFIX . self::MATERIAL_LIST_URL . 'access_token=' . $this->access_token;
		$param = ['type'=>$type,'offset'=>$offset,'count'=>$count];
		$result = $this->http_post($url, json_encode($param));
		if ($result)
		{
			$json = json_decode($result,true);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			if ($this->dubug) {
				$this->log($result,'获取永久素材列表');
			}
			return $json;
		}
		return false;
	}

	//获取永久素材总数
	public function getLongCount()
	{
		$url = self::API_URL_PREFIX . self::MATERIAL_COUNT_URL . 'access_token=' . $this->access_token;
		$result = $this->http_get($url);
		if ($result)
		{
			$json = json_decode($result,true);
			if (isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return false;
			}
			if ($this->dubug) {
				$this->log($result,'新增永久素材总数');
			}
			return $json;
		}
		return false;
	}

//=================用户管理

	//获取用户基本信息
	public function getUserInfo($openid)
	{
		$url = self::API_URL_PREFIX . self::USER_INFO_URL . 'access_token=' . $this->access_token . '&openid=' . $openid . '&lang=zh_CN';
		$result = $this->http_get($url);
		if ($this->dubug) {
			$this->log($result,'获取用户列表');
		}
		return $this->checkResult($result);
	}

	//获取用户列表
	public function getUserList()
	{
		$url = self::API_URL_PREFIX . self::USER_LIST_URL . 'access_token=' . $this->access_token;
		$result = $this->http_get($url);
		if ($this->dubug) {
			$this->log($result,'获取用户列表');
		}
		return $this->checkResult($result);
	}

	//设置用户备注名
	public function setUserName($openid,$remark)
	{
		$url = self::API_URL_PREFIX . self::USER_UPDATE_URL . 'access_token=' . $this->access_token;
		$data['openid'] = $openid;
		$data['remark'] = $remark;
		$result = $this->http_post($url, json_encode($data));
		if ($this->dubug) {
			$this->log($result,'设置用户备注名');
		}
		return $this->checkResult($result);
	}

	//用户标签管理
	
	//创建标签
	public function userTagCreate($tag)
	{
		$url = self::API_URL_PREFIX . self::TAGS_CREATE_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($tag,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'创建标签');
		}
		return $this->checkResult($result);
	}

	//获取已有标签
	public function userTagGet()
	{
		$url = self::API_URL_PREFIX . self::TAGS_GET_URL . 'access_token=' . $this->access_token;
		$result = $this->http_get($url);
		if ($this->dubug) {
			$this->log($result,'获取已有标签');
		}
		return $this->checkResult($result);
	}

	//修改用户标签
	public function userTagEdit($tagInfo)
	{
		$url = self::API_URL_PREFIX . self::TAGS_UPDATE_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($tagInfo,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'修改用户标签');
		}
		return $this->checkResult($result);
	}

	//删除标签
	public function userTagDelete($tagInfo)
	{
		$url = self::API_URL_PREFIX . self::TAGS_DELETE_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($tagInfo,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'删除标签');
		}
		return $this->checkResult($result);
	}

	//获取标签下粉丝
	public function getFansFromTag($tagInfo)
	{
		$url = self::API_URL_PREFIX . self::TAGS_FANS_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($tagInfo,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'获取标签下粉丝');
		}
		return $this->checkResult($result);
	}

	//批量为用户打标签
	public function userBatchTag($info)
	{
		$url = self::API_URL_PREFIX . self::TAGS_BATCH_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($info,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'批量为用户打标签');
		}
		return $this->checkResult($result);
	}

	//批量为用户取消标签
	public function userBatchUnTag($info)
	{
		$url = self::API_URL_PREFIX . self::TAGS_CANCLE_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($info,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'批量为用户取消标签');
		}
		return $this->checkResult($result);
	}

	//获取用户已有的标签列表
	public function userTagList($openid)
	{
		$url = self::API_URL_PREFIX . self::TAGS_LIST_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode($info,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'获取用户已有的标签列表');
		}
		return $this->checkResult($result);
	}


	//============菜单管理
	//
	//自定义菜单
	public function createMenu($data)
	{
		$url = self::API_URL_PREFIX . self::MENU_CREATE_URL . 'access_token=' . $this->access_token;
		// var_dump(json_encode($data));die;JSON_UNESCAPED_UNICODE
		$result = $this->http_post($url, json_encode($data,JSON_UNESCAPED_UNICODE));
		if ($this->dubug) {
			$this->log($result,'创建自定义菜单');
		}
		return $this->checkResult($result);
	}

	//查询自定义菜单
	public function getMenuInfo()
	{
		$url = self::API_URL_PREFIX . self::MENU_GET_URL . 'access_token=' . $this->access_token;
		$result = $this->http_get($url);
		if ($this->dubug) {
			$this->log($result,'查询自定义菜单');
		}
		return $this->checkResult($result);
	}

	//删除自定义菜单
	public function delMenu()
	{
		$url = self::API_URL_PREFIX . self::MENU_DELETE_URL . 'access_token=' . $this->access_token;
		$result = $this->http_get($url);
		if ($this->dubug) {
			$this->log($result,'删除自定义菜单');
		}
		return $this->checkResult($result);
	}

	//检查返回结果
	public function checkResult($result)
	{
		if ($result)
		{
			$json = json_decode($result,true);
			if (isset($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				if ($this->errMsg == 'ok') {
					return true;
				} else {
					return false;
				}
			}
			return $json;
		}
		return false;
	}
	
	/**
	 * GET 请求
	 * @param string $url
	 */
	private function http_get($url){
		$ch = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec($ch);
		$status = curl_getinfo($ch);
		curl_close($ch);
		if(intval($status["http_code"])==200){
			return $result;
		}else{
			return false;
		}
	}

	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @param boolean $post_file 是否文件上传
	 * @return string content
	 */
	private function http_post($url,$param,$post_file=false){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
				$is_curlFile = true;
		} else {
			$is_curlFile = false;
				if (defined('CURLOPT_SAFE_UPLOAD')) {
					curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
				}
		}
		if (is_string($param)) {
					$strPOST = $param;
			}elseif($post_file) {
					if($is_curlFile) {
						foreach ($param as $key => $val) {
								if (substr($val, 0, 1) == '@') {
									$param[$key] = new \CURLFile(realpath(substr($val,1)));
								} else {
									$param[$key] = $val;
								}
						}
					}
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		// var_dump($strPOST);die;
		$sContent = curl_exec($oCurl);
		// var_dump($sContent);die;
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	//清零API调用次数
	public function apiCountClear()
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/clear_quota?access_token=' . $this->access_token;
		$result = $this->http_post($url, json_encode(['appid'=>$this->appid]));
		if ($this->dubug) {
			$this->log($result,'清零API调用次数');
		}
		return $this->checkResult($result);
	}
}