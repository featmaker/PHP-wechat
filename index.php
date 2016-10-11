<?php
/**
  * wechat php test
  */

// define your token
require_once 'config.php';
require_once 'wechat.class.php';
$options = [
		'token' => TOKEN,
		'appid' => APPID,
		'appsecret' => APPSECRET,
		'logcallback' => false,
	];
$wechatObj = new WeChat($options);
// $data = ['@img/wallhaven-32872.png'];
// $info = $wechatObj->uploadTmp('image',$data);
// var_dump($info);
// $info = $wechatObj->getTmp('aU5FxVnwxpS0DJifJSD12X-RDJr6gK9IBGBz_wsZfmysHIWPgZthz-zok8YKeTKs');
// header( 'Content-Type: image/png' );
// echo $info;
// 
//{
  "articles": [{
       "title": TITLE,
       "thumb_media_id": THUMB_MEDIA_ID,
       "author": AUTHOR,
       "digest": DIGEST,
       "show_cover_pic": SHOW_COVER_PIC(0 / 1),
       "content": CONTENT,
       "content_source_url": CONTENT_SOURCE_URL
    },
    //若新增的是多图文素材，则此处应还有几段articles结构
 ]
}

$data = ['articles'=>['title'=>'shiyanlou',thumb_media_id]]
$info = $wechatObj->addMaterial('news',['@img/wallhaven-110.jpg']);
var_dump($info);
 // var_dump($info);
// imagepng($info);
// $wechatObj->valid();
// $msgType = $wechatObj->getRec()->getRecType();
// switch ($msgType) {
// 	case 'text':
// 		$content = $wechatObj->getRecContent();
// 		if ($content == 'ip') {
// 			$ip = $wechatObj->getServerIp();
// 			$ipstr = implode("\r\n",$ip);
// 			$wechatObj->text($ipstr)->reply();
// 		} elseif ($content == 'ig') {
// 			// $data = ['@img/wallhaven-110.jpg','@img/wallhaven-11829.jpg','@img/wallhaven-16301.jpg','@img/wallhaven-22670.jpg','@img/wallhaven-24695.jpg','@img/wallhaven-35475.jpg'];
// 			// foreach ($data as $key => $value) {
// 			// 	$info = $wechatObj->uploadImg([$value]);
// 			// 	var_dump($info);
// 			// }
// 			$info = $wechatObj->getMaterial('image',0,20);
// 			var_dump($info);
// 		}  else {
// 			$wechatObj->text('hello fuli')->reply();
// 		}
// 		break;
// 	case 'image':
// 		$imageInfo = $wechatObj->getRecPic();
// 		$wechatObj->image($imageInfo)->reply();
// 		break;
// 	case 'voice':
// 		$voiceInfo = $wechatObj->getRecVoice();
// 		$wechatObj->voice($voiceInfo)->reply();
// 		break;
// 	case 'shortvideo':
// 		$videoInfo = $wechatObj->getRecVideo();
// 		$videoInfo['title'] = '这是一段视频';
// 		$videoInfo['description'] = '家乐福及数量发生发送到了法律上多久了丰盛的是';
// 		$wechatObj->video($videoInfo)->reply();
// 		break;
// 	case 'uploadimg':

// 		break;
// 	default:
// 		# code...
// 		break;
// }
// $wechatObj->valid();
