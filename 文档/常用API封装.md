# 常用API封装

## 一、实验简介

### 1.1 实验目的

本实验以编码为主要内容，封装微信公众平台常用API接口，开发一个简易的SDK。主要设计的功能：消息管理，自定义菜单，素材管理，用户管理。我们将以这几个功能为基础，熟悉如何调用API，如何处理与微信服务器之间的数据交互。为了实验的简单，我们不使用前台界面，不使用数据库，只关心PHP的功能编码。

### 1.2 实验环境与工具

实验楼在线环境：Linux

本地服务器：Apache

```sh
sudo service apache2 start
```

公网与本地服务映射工具：ngrok

```sh
./ngrok http 80
```

代码编辑器：会员推荐 WebIDE，普通用户推荐 Sublime 文本编辑器

时刻准备着开发者文档：[点击查看](https://mp.weixin.qq.com/wiki)

## 二、实验过程

在上一个实验中，我们介绍了微信公众平台的相关知识，并且成功接入公众平台到我们自己的服务器，本次实验承接以上内容，开始正式的开发内容。

---

### 2.1 消息管理

> 接受普通消息

开发者文档关于这部分的描述：[接收普通消息](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140453&token=&lang=zh_CN)

当成功接入公众平台以后，那么微信服务器就成了一个中继站，负责开发者服务器与用户之间的信息传递。当用户在手机端给公众号发送消息，微信会将信息处理为XML格式的数据，发送到开发者服务器。这里有几点需要注意：

> 1、关于重试的消息排重，推荐使用msgid排重。
>
> 2、微信服务器在五秒内收不到响应会断掉连接，并且重新发起请求，总共重试三次。假如服务器无法保证在五秒内处理并回复，
>
> 可以直接回复空串，微信服务器不会对此作任何处理，并且不会发起重试。详情请见“发送消息-被动回复消息”。
>
> 3、如果开发者需要对用户消息在5秒内立即做出回应，即使用“发送消息-被动回复消息”接口向用户被动回复消息时，可以在
>
> 公众平台官网的开发者中心处设置消息加密。开启加密后，用户发来的消息和开发者回复的消息都会被加密（但开发者通过客服
>
> 接口等API调用形式向用户发送消息，则不受影响）。关于消息加解密的详细说明，请见“发送消息-被动回复消息加解密说明”。

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2198timestamp1476673634745.png/wm)

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2198timestamp1476673697183.png/wm)

上图显示，我们确实收到了来自微信服务器通过POST请求的方式传来的XML数据。

接收到的XML数据格式讲解：

> | ToUserName   | 开发者微信号          |
> | ------------ | --------------- |
> | FromUserName | 发送方帐号（一个OpenID） |
> | CreateTime   | 消息创建时间 （整型）     |
> | MsgType      | text            |
> | Content      | 文本消息内容          |
> | MsgId        | 消息id，64位整型      |

此外，消息的类型不限于文本消息，还可以是图片，视频，语音，短视频，链接以及地理位置等，所以我们需要设计一个方法来接受这些消息并判断消息的类型，再做后续处理。

首先，在 `wechat.class.php` 类里定义消息类型常量。

```php
	//消息类型
	const MSGTYPE_TEXT = 'text';
	const MSGTYPE_IMAGE = 'image';
	const MSGTYPE_MUSIC = 'music';
	const MSGTYPE_VOICE = 'voice';
	const MSGTYPE_NEWS = 'news';
	const MSGTYPE_VIDEO = 'video';
	const MSGTYPE_SHORTVIDEO = 'shortvideo';
	const MSGTYPE_URL = 'url';
	const MSGTYPE_LOCATION = 'location';
```

定义API接口的URL地址，这里暂时定义一部分，后续在此基础上添加：

```php
	//通用api接口前缀,所以API接口地址，均以此为开头
	const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin/';
	//获取凭证
	const GET_TOKEN_URL = 'token?';
```

然后声明如下成员变量：

```php
	private $token;	//接入公众平台时设置的token
	private $appid;	//开发者公众号appID
	private $appsecret;	//开发者公众号appsecret
	private $access_token;	//接口调用凭证
	private $msg;	//回应的消息
	private $receive;	//接收的消息数据
	public $errMsg = "Hello shiyanlou";	//错误信息
	public $errCode=-1;	//错误代码
	public $dubug;	//是否调试（日志记录）
```

初始化构造函数：

```php
	function __construct($options = [])
	{
		$this->token = isset($options['token'])?$options['token']:'';
		$this->appid = isset($options['appid'])?$options['appid']:'';
		$this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
		$this->dubug = isset($options['dubug'])?$options['dubug']:false;
		if ($this->checkExpire(7200)) {		//检查access_token 是否过期
			$this->getAccessToken();
		}
		$this->access_token = $this->getTokenByCache();	//从缓存中获取 access_token
```

---

获取接口凭证 `access_tokne` ：

因为 `access_token` 是接口调用凭证，调用所有接口时都需要加上他用来确定我们的身份。每个 `access_token` 的有效期为 7200s，超过这个期限，就需要更新 `access_token` 的值。我在初始化构造函数中，首先检查缓存的 `access_token` 是否过期，若过期，则重新获取，最后从缓存中获取 `access_token` 的值；

我把获取 `access_token` 的相关方法讲解一下，官方文档部分：[获取access_token](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140183&token=&lang=zh_CN)：

```php
	//获取AccessToken
	public function getAccessToken($appid='',$appsecret='')
	{
		if (!$appid || !$appsecret) {
			$appid = $this->appid;
			$appsecret = $this->appsecret;
		}
    //请求地址：https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET
		$url = self::API_URL_PREFIX . self::GET_TOKEN_URL . 'grant_type=client_credential&appid=' . $appid . '&secret=' . $appsecret;
		$result = $this->http_get($url);		//构造get请求，后面会讲解这个方法
		if ($result) {
			$this->log($result,'获取 access_token');		//写入日志，后面会讲解这个方法
			$result = json_decode($result);		//使用json解析获取的数据
			$this->cache($result->access_token);	//写入缓存
			return true;
		} else {
			return false;
		}
	}
```

上述方法，通过拼接字符串组合 $url 地址，get请求获取返回的数据，日志记录，并且写入缓存，需要使用的时候，直接调用函数从缓存中读取就行。上面还用到了一个自定义方法 `http_get(url)` ，这是用来执行 get 请求，与之对应的还有 `http_post()` 方法。这个我们后面再讲解。

因为我们将 `access_token` 定义成私有属性，外部是不能直接访问他的值，所以我们可以对外提供一个接口，用来查询 `access_token` 的值：

```php
	public function getToken()
	{
		return $this->access_token;
	}
```

上面提到了缓存的相关方法，我们这里实现一下。为了方便，我直接使用文件缓存的方式，其实还有很多其他方式保存 `access_token` 的值，比如使用数据库，或者使用 redis 或  memcached 等众多更好的方法。我直接采用了最简单最原始的方法，使用文件缓存。

```php
	//缓存access_token数据
	public function cache($value)
	{
		$file = fopen('./access_token.txt','w+');	//w+ 方式打开文件，不存在则尝试创建
		if ($file) {
			fwrite($file,$value);
			fclose($file);
			return true;
		}
	}
```

既然可以存入，那么也应该可以读取：

```php
//从缓存中获取acces_token
	public function getTokenByCache()
	{
		if (file_exists('./access_token.txt')) {	//缓存文件存在
			$content = file_get_contents('./access_token.txt');		//读取文件内容
			if ($content == '') {	//内容为空，从新获取
				$this->getAccessToken();
				return file_get_contents('./access_token.txt');
			} else {
				return file_get_contents('./access_token.txt');
			}
		} else {		//缓存文件不存在，从新获取并缓存
			$this->getAccessToken();
			return file_get_contents('./access_token.txt');
		}
	}
```

其实上面的操作还算不上真正的缓存，只是为了保存 `access_token` 的值而已，前面提到过，每次调用 API 接口，都需要加上 `access_token` 参数，而每次微信服务器与开发者服务器交互，都是独立的，所以没办法使用 session 来保存数据。因为 `access_tokne` 具有7200s的有效时间，所以在有效期内，我们可以不必再重复请求获取，而且每天获取 `access_token` 的次数是有限制的，所以我们需要有一个地方将获得 `access_token` 的值保存起来，可以使用数据库，或者redis 等缓存技术，下次需要的时候，直接取出来用，如果过了有效期，此时再重新获取就行。我这里是直接将获取的 `access_token` 的值写入 `access_token.txt` 。接下来还需要一个方法来判断数据是否过期：

```php
//检查access_token 过期时间
	public function checkExpire($time = 7200)
	{
		$file = './access_token.txt';
		if (file_exists($file)) {		//文件存在
			if (time() - filemtime($file) > $time) {	//利用文件最后修改时间判定过期
				return true;
			} else {
				return false;
			}
		} else {		//文件不存在，没有过期
			return false;
		}
	}
```

为了测试顺利通过，我把上面用到的两个未定义的方法在这里写一下，分别是 `log()` 日志记录函数和 `http_get()` get 网络请求方法：

```php
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
```

```php
/**
	 * GET 请求
	 * @param string $url
	 */
	private function http_get($url){		//都是一些基本的 curl 操作，不清楚的同学自己去查一下资料
		$ch = curl_init();
		if(stripos($url,"https://")!==FALSE){		//https：// ,绕过证书验证
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
```

好了，通过以上方法，我们就可以获得有效地 `access_token` 。我们可以简单测试一下。

为了配置的方便，我建议大家单独写一个配置文件，里面填写开发者公众号的配置信息：

`config.php` ：

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2198timestamp1476688537253.png/wm)

在 `index.php` 中测试一下：

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2198timestamp1476688637899.png/wm)

打开 `127.0.0.1` ，如果一切正常的话，浏览器应该会显示一串很长的字符串，而且打开你的 `access_token.txt` ，里面也保存着同样的字符串。同时，你还将得到一个 `log.txt` 的日志文件，上面记录了你刚才的操作和得到的数据。说明你做的一切都是顺利的，可以继续向下写，如果有问题，请仔细检查错误位置和错误信息，自行解决。

---

上面写了很多代码去获取接口凭证，好了，现在我们可以准确拿到凭证，后面的代码编写就顺利多了。还是接着刚才的话题继续说：`接受普通消息`

首先，我们需要接受来自微信服务器的转发内容：

```php
//获取接收到的信息
	public function getRec()
	{
		if ($this->receive) {		//如果存在接受消息，则返回
			if ($this->dubug){		//是否调试，记录日志
				$this->log($this->receive,'接收');
			}
			return $this;
		}
		$postStr = file_get_contents("php://input");		//获取数据
		if ($this->dubug){		//接受数据写入日志
			$this->log($postStr,'接收');
		}
		if (!empty($postStr)) {
			$this->receive = (array)simplexml_load_string($postStr,'SimpleXMLElement', LIBXML_NOCDATA);	//将 xml 数据转为数组形式
		}
		return $this;
	}
```

上面使用 `file_get_contents("php://input")` 来接受数据：

> Coentent-Type仅在取值为application/x-www-data-urlencoded和multipart/form-data两种情况下，PHP才会将http请求数据包中相应的数据填入全局变量$_POST 
>
> PHP不能识别的Content-Type类型的时候，会将http请求包中相应的数据填入变量$HTTP_RAW_POST_DATA 
>
> 只有Coentent-Type为multipart/form-data的时候，PHP不会将http请求数据包中的相应数据填入php://input，否则其它情况都会。填入的长度，由Coentent-Length指定。 
>
> php://input数据总是跟$HTTP_RAW_POST_DATA更凑效，且不需要特殊设置php.ini 

简单来说，就是一般的 `$_GET()/$POST()` 只能识别一般规则的数据，即在 `application/x-www-data-urlencoded和multipart/form-data两种情况下` 才能通过 `$_GET()/$_POST()` 访问数据，否则，需要使用 `$HTTP_RAW_POST_DATA ` 或者 `php://input`来获取输入内容，但是一般来说更偏好于使用 `php://input` 。从之前的数据来看，微信服务器传给我们的数据是 `XML` 格式的，所以我们不能通过 $_POST() 来获取内容。

当得到 xml 数据之后，我们需要将它处理，变成我们方便使用的格式-数组。为了安全考虑，xml 数据中包含 `<![CDATA[标题]]>` 这样的内容传递数据，具体的原因大家自行去了解，所以我们解析的时候需要去掉这些内容，所以这就是 `simplexml_load_string($postStr,'SimpleXMLElement', LIBXML_NOCDATA)` 中第三个参数的作用。最后，将得到的对象通过类型转换为数组形式，赋值给 `$receive` 成员变量。

最后，我们返回的 $this ，返回对象本身，所以我们的操作是支持链式操作的。

同接口凭证类似，我们也需要对外开发一个访问接口，允许访问我们接受的数据：

```php
//获取接受数据
	public function getReceiveDate()
	{
		return $this->receive;
	}
```

现在我们测试一下效果，在 `index.php` 中稍作修改：

```php
var_dump($wechatObj->getRec()->getReceiveDate());
```

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2198timestamp1476691488070.png/wm)

在你的手机微信上，给你的接口测试号发送一条信息，在进入 `ngrok` 控制面板，查看请求内容：

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2198timestamp1476691633235.png/wm)

我们成功的获取到了xml数据，并转化为我们方便操作的数组格式。数组中包含的字段信息有：接受者，发送者，消息创建时间，消息类型，消息内容，消息ID。这里面所有的消息，对我们都是有用的，所以为了方便信息的获取，我们可以自定义一些方法来取得这些字段信息：

```php
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
```

上面的几个方法获取的是通用的字段信息，微信服务器发来的所有消息都至少包含了这几项内容。当然，随着消息类型的不同，消息的部分内容也是不同的，之前提到过，可能有视频，图片，语音等类型的消息，所以我们需要单独读取内容：

```php
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

```

不同的消息类型有着不同的字段内容，我这里不多说，大家自己去查看开发者文档：[开发者文档](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140453&token=&lang=zh_CN) ，了解不同格式的数据，他们的xml数据有什么异同，大家也可以在记得手机上尝试，给接口测试号发送不同类型的消息，在 `ngrok` 控制面板中查看各种数据内容。

---

**实现文本回复**

通过上面的方法，我们获得了 xml 中的相关信息，那么我们就可以利用这些信息，给用户发送回复内容，包括但不限于文本内容，在开发者文档中关于消息回复部分的内容：[消息被动回复](https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140543&token=&lang=zh_CN) 。在公众号中，一般来说，我们只能在收到用户给我们发来信息之后执行被动回复，当然也可以主动群发消息（数量有限）。或者通过微信公众平台界面操作，可以和用户自由聊天。否则是不能和用户直接对话。接下来我们就试一下被动回复内容的效果：即用户发送一条文字消息，我们回复他一条文字消息。

根据开发者文档的说明，回复的文本消息只能是如下xml格式：

```xml
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>12345678</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[你好]]></Content>
</xml>
```

所以，我们需要想办法将我们的数据转化为上面的格式。

因为我们获取消息时是把xml转为数组，所以我们回复的时候可以考虑将数据放到一个数组里，再将这个数组转为xml格式，再输出就行。

首先，设置我们要发送的文本内容：

```php
	//设置文本信息
	public function text($text = '')
	{
    //将要发送的信息放到数组里
		$msg = [
			'ToUserName' => $this->getRecFrom(),		//将源消息的发送者设为我们将要送达消息的接受者
			'FromUserName'=>$this->getRecTo(),		//源消息的接受者是我们自己，现在设置为发送者
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_TEXT,	//文本消息类型
			'Content'=>$this->textFilter($text),	//字符串过滤
		];		
		$this->message($msg);	//处理要发送的数据
		return $this;
	}
```

当用户给我们发来一条信息，我们就可以获取消息的来源和送达地址，发送者是用户，接受者是我们自己。现在我们回复的时候，就需要将这两项调整一下顺序，将发送者设为我们自己，将接受者设为刚才发消息的用户，这样，我们的消息才能准确回复。

上面用到了一个字符串过滤的方法，用来过滤一些特殊的字符，保证数据的安全：

```php
	/**
	 * 过滤文字回复\r\n换行符
	 * @param string $text
	 * @return string|mixed
	 */
	private function textFilter($text) {
		return str_replace("\r\n", "\n", $text);
	}
```

当我们设置好数组形式的信息之后，我们还调用了一个 `message()` 方法进一步处理数组信息：

```php
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
```

通过这个方法可以多次设置发送的消息内容，而且，如果还有其他的字段信息，也可以通过这个方法追加到回复信息中去。不过如果是存在相同键的数组，后者将会覆盖前者。最后将回复消息赋值给 $msg 成员变量。

最后，我们再写一个回复消息的方法，通过这个方法，将我们要发送的数组信息转换为xml信息并输出：

```php
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
		$xmlData = $this->xml_encode($this->msg);	//将数组转为xml编码
		if ($this->dubug) {
			$this->log($xmlData,'回复');
		}
		echo $xmlData;
	}
```

上面调用了一个方法：`xml_encode()` 。目的是将消息数组转为xml数据。

```php
	//xml格式编码
	public function xml_encode($data,$root = 'xml',$attr = '',$encoding='utf-8')
	{
		if (is_array($attr)) {
			$attr1 = [];
			foreach ($attr as $key => $value) {		//节点属性，默认为空
				$attr1[] = "{$key}=\"{$value}\"";
			}
			$attr = implode(' ',$attr1);
		}
		$xml = '';
		$attr = empty($attr) ? '' : trim($attr);
		$xml .= "<{$root}{$attr}>";		//添加根节点开始标签
		$xml .= self::dataToXml($data);		//数组转xml
		$xml .= "</{$root}>";	//添加根节点结束标签
		return $xml;	//返回xml数据
	}
```

上面的方法主要是添加xml数据的根节点标签和属性，真正起作用的方法是 `self::dataToXml()` 方法，这才是最重要的方法：

```php
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
```

通过以上方法，就可以将一个数组转换为一个xml格式的数据，其中这一句代码可能比较费解：

```php
is_numeric($key) && $key = "item id=\"$key\"";
```

这就是 `if() {} else {}` 的简化版，你可以自己去测试一下这句代码，你就知道他的原理是什么了。总的来说，上面的方法不过是对数组的遍历操作和字符串的拼接操作而已，并没有什么实质性的技术难度，稍微多花一点时间就能理解。仔细观察上面的代码，你会发现一个陌生的方法：`self::safeXmlStr($value)` ，看名字也可以猜出他的作用，就是过滤安全的xml字符串：

```php
	//去掉控制字符
	public static function safeXmlStr($str)
	{
		return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
	}
```

> 一般xml中如果含有&等字符，可以通过CDATA来过滤，但是含有一些不认识的特殊字符时候就会不起作用，需要自己想办法来过滤xml中的非法字符:
> //XML标准规定的无效字节为：
>
> /*
> 0x00 – 0x08
> 0x0b – 0x0c
> 0x0e – 0x1f
> */
>
> 所以上面的正则表达式就是这个作用，如果你对正则表达式不是很熟悉的话，建议你去学习实验楼提供的正则表达式的相关技术课程。

通过以上方法的层层处理，我们的终于可以尝试给用户发送消息了。

在 `index.php` 中，修改如下：

```php
<?php
/**
  * wechat php test
*/
require_once 'config.php';
require_once 'wechat.class.php';
$options = [
		'token' => TOKEN,
		'appid' => APPID,
		'appsecret' => APPSECRET,
		'dubug' => true,
	];
$wechatObj = new WeChat($options);
if (isset($_GET["echostr"])) {
	$wechatObj->valid();
}
//==============================后续的功能测试，只修改这之后的内容，前面部分保留，============
// echo $wechatObj->getToken();
// 打印接受到的数据
// var_dump($wechatObj->getRec()->getReceiveDate());
$wechatObj->getRec();		//获取发来的消息数据
$wechatObj->text("你好，我是微信机器人，来自自动回复!")->reply();		//回复内容
```

修改完成之后，通过你的手机微信给测试公账号发送一条消息：

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2199timestamp1476698430977.png/wm)

接下来看看控制面板中，数据的交互：

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2199timestamp1476698565422.png/wm)

所以，我们成功的完成了消息自动回复功能。

当然，目前为止，我们只实现了回复文本消息，不过我们可以按照这个规律回复其他类型的消息，有一点需要注意的就是，不同类型的消息，xml节点有所区别，具体的区别可以查看开发者文档了解更多：

```php
	//回复图片消息
	public function image($mediaid)
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_IMAGE,
			'Image'=>['MediaId'=>$mediaid]
		];
		$this->message($msg);
		return $this;
	}

	//回复语音消息
	public function voice($mediaid)
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_VOICE,
			'Voice'=>['MediaId'=>$mediaid]
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

```

其中，要回复媒体数据时，需要使用使用到 `mediaid` 信息，这个你们暂时是没有的，不过我们后面会教大家如何获取这个信息。

实现 `图尚往来` 功能：

为了大家能进一步理解消息回复功能，这里我再实现一个功能，回复图片，因为我们还没有自己管理的素材，所以没办法获取 `mediaid` 信息，但是我们可以获取他人的 `mediaid` 实现发送功能，当然，其他的比如语音，音乐和视频都可以按照这个方法来实现回复。

在上面给出的代码中，有一个方法叫做 `getRecPic()` 获取接受的图片，当然还有获取视频，获取语音等方法。

```php
	//获取消息图片信息
	public function getRecPic()
	{
		if (isset($this->receive['PicUrl'])) {
			return ['mediaid'=>$this->receive['MediaId'],'picurl'=>$this->receive['PicUrl']];
		} else {
			return false;
		}
	}
```

按照开发者文档给出的说明，当用户发来图片是，我们收到的xml数据为一下格式：

```xml
<xml>
 <ToUserName><![CDATA[toUser]]></ToUserName>
 <FromUserName><![CDATA[fromUser]]></FromUserName>
 <CreateTime>1348831860</CreateTime>
 <MsgType><![CDATA[image]]></MsgType>
 <PicUrl><![CDATA[this is a url]]></PicUrl>
 <MediaId><![CDATA[media_id]]></MediaId>
 <MsgId>1234567890123456</MsgId>
 </xml>
```

从数据中可以看到，里面包含了 `mediaid` 这个节点的信息，这正是我们想要的，通过上面的方法就可以得到这些信息，得到之后，通过我们的回复图片信息的方法，传入获得的 `mediaid` 数据：

```php
	//回复图片消息
	public function image($mediaid)
	{
		$msg = [
			'ToUserName' => $this->getRecFrom(),
			'FromUserName'=>$this->getRecTo(),
			'CreateTime'=>time(),
			'MsgType'=>self::MSGTYPE_IMAGE,
			'Image'=>['MediaId'=>$mediaid]
		];
		$this->message($msg);
		return $this;
	}
```

就可以获得图片消息的回复，在注释之前的测试代码，添加以下代码：

```php
$wechatObj->getRec();
$imageInfo = $wechatObj->getRecPic();
$wechatObj->image($imageInfo['mediaid'])->reply();
```

效果：

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2199timestamp1476700761437.png/wm)

![此处输入图片的描述](https://dn-anything-about-doc.qbox.me/document-uid108299labid2199timestamp1476700799287.png/wm)

同理，你也可以回复任意视频，语音等媒体消息。

