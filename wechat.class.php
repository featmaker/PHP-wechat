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

	//客服管理
	const KF_URL_PREFIX = 'https://api.weixin.qq.com/customservice/';
	const KF_ADD_URL = 'kfaccount/add?';
	const KF_UPDATE_URL = 'kfaccount/update?';
	const KF_DEL_URL = 'kfaccount/del?';
	const KF_LIST_URL = 'getkflist?';
	const KF_HEADIMG_URL = 'kfaccount/uploadheadimg?';
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
	const TAGS_USER_URL = 'tags/getidlist?';

	const USER_INFO_URL = 'user/info?';
	const USERS_INFO_URL = 'user/info/batchget?';
	const USER_LIST_URL = 'user/get?';
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
	private $postxml;
	private $user_token;
	private $encodingAesKey;
	private $encrypt_type;
	public $errMsg = "Hello shiyanlou";
	public $errcode=-1;
	public $logcallback;
	public  static $last_time;


	function __construct($options = [])
	{
		$this->token = isset($options['token'])?$options['token']:'';
		// $this->encodingAesKey = isset($options['encodingaeskey'])?$options['encodingaeskey']:'';
		$this->appid = isset($options['appid'])?$options['appid']:'';
		$this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
		$this->logcallback = isset($options['logcallback'])?$options['logcallback']:false;
		self::$last_time = (self::$last_time == null) ? time() : self::$last_time;
		$this->checkTokenExpires();
	}

	//接入验证
	public function valid()
	{
		$echoStr = $_GET["echostr"];
		//valid signature , option
		if($this->checkSignature()){
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

	//检查AccessToken是否过期
	public function checkTokenExpires()
	{
		if (!isset($this->access_token)) {
			$this->getAccessToken();
			return true;
		}
		$now = time();
		$diff = intval((($now - self::$last_time)%3600)/60);
		if ($diff >= 90) {
			if ($this->getAccessToken()) {
				return true;
			} else {
				return false;
			}
		}
		return true;

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
		if ($this->logcallback) {
			$this->log($xmlData);
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
	public function log($data)
	{
		//TODO
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
			return $this;
		}
		$postStr = !empty($this->postxml) ? $this->postxml : file_get_contents("php://input");
		$this->log($postStr);
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
			$event['key'] = $this->receive['Event'];
		}
		if (isset($this->receive['Ticket'])) {
			$event['Ticket'] = $this->receive['Ticket'];
		}
		if (isset($event) && !empty($event)) {
			return $event;
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
				return $false;
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
				break;
			case 'image':
				$url = self::API_URL_PREFIX . self::MEDIA_ADDIMG_URL . 'access_token=' . $this->access_token;
				break;
			case 'material':
				if ($is_video) {
					$data['description'] = json_decode($info);
				}
				$url = self::API_URL_PREFIX . self::MATERIAL_ADDMATERIAL_URL . 'access_token=' . $this->access_token . '&type=' . $materialType;
				break;
			default:
				return false;
				break;
		}
		$result = $this->http_post($url, $data, true);
		if ($result) {
			$json = (array)json_decode($result);
			if (!$json || !empty($json['errcode'])) {
				$this->errCode = $json['errcode'];
				$this->errMsg = $json['errmsg'];
				return $false;
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
		$result = $this->http_post($url, $data, false);
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
                    return $json;
                } else {
                    return $result;
                }
            }
            return $result;
        }
        return false;
	}

	//删除永久素材
	public function delMaterial($mediaid)
	{
		$url = self::API_URL_PREFIX . self::MATERIAL_DELMATERIAL_URL . 'access_token=' . $this->access_token;
		$data['media_id'] = $mediaid;
		$result = $this->http_post($url, $data, false);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
	}

	//修改永久图文素材
	public function updateNews($mediaid,$index,$data)
	{
		$data['media_id'] = $mediaid;
		$data['index'] = $index;
		$url = self::API_URL_PREFIX . self::MATERIAL_UPDATENEWS_URL . 'access_token=' . $this->access_token;
		$result = $this->http_post($url, $data);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
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
		$result = $this->http_post($url, $param);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
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
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
}