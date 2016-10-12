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
// $info = $wechatObj->apiCountClear();
// var_dump($wechatObj->errCode);
// var_dump($wechatObj->errMsg);
// var_dump($info);
// die;
// $wechatObj->valid();
// die;

//=================接口测试======================

                //上传临时素材
//上传图片素材(yes)
// $data = ['@img/w110.jpg'];
// $info = $wechatObj->uploadTmp('image',$data);
//{ ["type"]=> string(5) "image" ["media_id"]=> string(64) "R_hwAih90QbedV9SyKJy210uOI27uRKZvrNRMBcCSrXNV6wI3rk83cjUePgmcPGe" ["created_at"]=> int(1476193867) }
//=====================================
//上传音频素材(yes)
// $data = ['@audio/cut.mp3'];
// $info = $wechatObj->uploadTmp('voice',$data);
// { ["type"]=> string(5) "voice" ["media_id"]=> string(64) "3aQ3GdVnjPEXCSEtYuW8udXP4JajBWyllM95APV5H5pqjDoa1ffPAAIjDVG2uBmU" ["created_at"]=> int(1476194919) }
//======================================
//上传缩略图(yes)
// $data = ['@img/tx.jpg'];
// $info = $wechatObj->uploadTmp('thumb',$data);
// { ["type"]=> string(5) "thumb" ["thumb_media_id"]=> string(64) "-aTLKtqCt-dGHYPAFXfd2wZUMboDU65QyAwb88whlAGcG2cy8ufo1f4a1p9iDmW3" ["created_at"]=> int(1476195230) }
//======================================
//上传视频(yes)
// $data = ['@mp4/test.mp4'];
// $info = $wechatObj->uploadTmp('video',$data);
// { ["type"]=> string(5) "video" ["media_id"]=> string(64) "pDgBd31gaIA--SjKkqqZbgZkOhvQOjGY0JFkU-CWWvBtTp2ssoigSq1KKftHQCuD" ["created_at"]=> int(1476195642) }

                //获取临时素材
//获取素材(yes)
// $mediaid = "R_hwAih90QbedV9SyKJy210uOI27uRKZvrNRMBcCSrXNV6wI3rk83cjUePgmcPGe";
// $mediaid = "3aQ3GdVnjPEXCSEtYuW8udXP4JajBWyllM95APV5H5pqjDoa1ffPAAIjDVG2uBmU";
// $mediaid = "-aTLKtqCt-dGHYPAFXfd2wZUMboDU65QyAwb88whlAGcG2cy8ufo1f4a1p9iDmW3";
// $mediaid = "pDgBd31gaIA--SjKkqqZbgZkOhvQOjGY0JFkU-CWWvBtTp2ssoigSq1KKftHQCuD";
// $result = $wechatObj->getTmp($mediaid);

                //添加永久素材
//添加图片
// $data = ['@img/w110.jpg'];
// array(1) { ["url"]=> string(124) "http://mmbiz.qpic.cn/mmbiz_jpg/eNkf5QIHGdw3bUTQbbehP2ibemFcnFR13y9Xp6VAVpiaia96YX4fw2QMFGx15XUAYQg8QHEtUK3lG5lKrSicrQkiaWA/0" }
//添加音频
// $data = ['media'=>'@audio/cut.mp3'];
// array(1) { ["media_id"]=> string(43) "oCmCA6JW8AUym2t9uhqzeFd28BpCHYsj5fQZGoaVuDA" }
//添加缩略图
// $data = ['media'=>'@img/tx.jpg'];
//array(2) { ["media_id"]=> string(43) "oCmCA6JW8AUym2t9uhqzeIzNHOLe7GNsO2fvhAsuUtI" ["url"]=> string(133) "http://mmbiz.qpic.cn/mmbiz_jpg/eNkf5QIHGdw3bUTQbbehP2ibemFcnFR13nWKJfLusYNl1RkbibesFqLP13gD87u1sDbnmuaEfWCp4nYR8grtsnAw/0?wx_fmt=jpeg" }
//添加视频
// $data = ['media'=>'@mp4/test.mp4'];
// $desc = ['title'=>'shiyanlou','introduction'=>'woshi yige ce shi'];
// $info = $wechatObj->addMaterial('video',$data,true,$desc);
// array(1) { ["media_id"]=> string(43) "oCmCA6JW8AUym2t9uhqzeKtiYQzppXBMyn6U2k2IMmU" }
// 添加图文
// $data = ['articles'=>
// 				[
// 					[
// 						'title'=>'test',
// 						'thumb_media_id'=>'oCmCA6JW8AUym2t9uhqzeIzNHOLe7GNsO2fvhAsuUtI',
// 						'author'=>'fuli',
// 						'digest'=>'dddfdsfds',
// 						'show_cover_pic'=>'1',
// 						'content'=>'fdssssssssgaaaaaaaaaaaaeg',
// 						'content_source_url'=>'http://www.baidu.com'
// 					]
// 				]
// 		];
// $info = $wechatObj->addMaterial('news',$data);
// { ["media_id"]=> string(43) "oCmCA6JW8AUym2t9uhqzeEHdfvJ_Z0MUoqUjOWWEOrQ" }
// { ["media_id"]=> string(43) "oCmCA6JW8AUym2t9uhqzeBh1UQrvEdzCMOSp53eujfY" }
// 永久素材数
// $info = $wechatObj->getLongCount();
// 获取图文
// $info = $wechatObj->getMaterial('oCmCA6JW8AUym2t9uhqzeBh1UQrvEdzCMOSp53eujfY');
// 获取视频
// $info = $wechatObj->getMaterial('oCmCA6JW8AUym2t9uhqzeKtiYQzppXBMyn6U2k2IMmU');
// 删除素材
// $info = $wechatObj->delMaterial('oCmCA6JW8AUym2t9uhqzeFd28BpCHYsj5fQZGoaVuDA');
// 修改永久图文
// $data = [	
// 			'articles'=>
// 					[
// 						'title'=>'testbyfuli',
// 						'thumb_media_id'=>'oCmCA6JW8AUym2t9uhqzeIzNHOLe7GNsO2fvhAsuUtI',
// 						'author'=>'fuli',
// 						'digest'=>'dddfdsfds',
// 						'show_cover_pic'=>'1',
// 						'content'=>'fdssssssssgaaaaaaaaaaaaeg',
// 						'content_source_url'=>'http://www.baidu.com'
// 					]
// 		];
// $info = $wechatObj->updateNews('oCmCA6JW8AUym2t9uhqzeEHdfvJ_Z0MUoqUjOWWEOrQ',0,$data);

var_dump($wechatObj->errCode);
var_dump($wechatObj->errMsg);
var_dump($info);


// $data = ['@img/wallhaven-32872.png'];
// $info = $wechatObj->uploadTmp('image',$data);
// var_dump($info);
// $info = $wechatObj->getTmp('aU5FxVnwxpS0DJifJSD12X-RDJr6gK9IBGBz_wsZfmysHIWPgZthz-zok8YKeTKs');
// header( 'Content-Type: image/png' );
// echo $info;
// 
// $data = '{
//   "articles": [{
//        "title": "woofs",
//        "thumb_media_id": "oCmCA6JW8AUym2t9uhqzeIzNHOLe7GNsO2fvhAsuUtI",
//        "author": "fuli",
//        "digest": "fdsfsd",
//        "show_cover_pic": 1,
//        "content": "ffffffffffffffffs",
//        "content_source_url": "www.baidu.com"
//     }
//  ]
// }';
// {
// 	"articles":{
// 		"title":"test",
// 		"thumb_media_id":"oCmCA6JW8AUym2t9uhqzeIzNHOLe7GNsO2fvhAsuUtI",
// 		"author":"fuli",
// 		"digest":"dddfdsfds",
// 		"show_cover_pic":0,
// 		"content":"fdssssssssgaaaaaaaaaaaaeg",
// 		"content_source_url":"http:\/\/www.baidu.com"
// 	}
// }
// $data = ['articles'=>['title'=>'shiyanlou',thumb_media_id]];
// $info = $wechatObj->addMaterial('news',['@img/wallhaven-110.jpg']);
// var_dump($info);
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
// 			$wechatObj->text('hello 曹大猛')->reply();
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
