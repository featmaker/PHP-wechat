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






// $wechatObj->valid();
// $data = ['@img/wallhaven-32872.png'];
// $info = $wechatObj->uploadTmp('image',$data);
// var_dump($info);
// $info = $wechatObj->getTmp('aU5FxVnwxpS0DJifJSD12X-RDJr6gK9IBGBz_wsZfmysHIWPgZthz-zok8YKeTKs');
// header( 'Content-Type: image/png' );
// echo $info;
// 
//{
//   "articles": [{
//        "title": TITLE,
//        "thumb_media_id": THUMB_MEDIA_ID,
//        "author": AUTHOR,
//        "digest": DIGEST,
//        "show_cover_pic": SHOW_COVER_PIC(0 / 1),
//        "content": CONTENT,
//        "content_source_url": CONTENT_SOURCE_URL
//     },
//     //若新增的是多图文素材，则此处应还有几段articles结构
//  ]
// }

// $data = ['articles'=>['title'=>'shiyanlou',thumb_media_id]];
// $info = $wechatObj->addMaterial('news',['@img/wallhaven-110.jpg']);
// var_dump($info);
 // var_dump($info);
// imagepng($info);
// $wechatObj->valid();
$msgType = $wechatObj->getRec()->getRecType();
switch ($msgType) {
	case 'text':
		$content = $wechatObj->getRecContent();
		if ($content == 'ip') {
			$ip = $wechatObj->getServerIp();
			$ipstr = implode("\r\n",$ip);
			$wechatObj->text($ipstr)->reply();
		} elseif ($content == 'ig') {
			// $data = ['@img/wallhaven-110.jpg','@img/wallhaven-11829.jpg','@img/wallhaven-16301.jpg','@img/wallhaven-22670.jpg','@img/wallhaven-24695.jpg','@img/wallhaven-35475.jpg'];
			// foreach ($data as $key => $value) {
			// 	$info = $wechatObj->uploadImg([$value]);
			// 	var_dump($info);
			// }
			$info = $wechatObj->getMaterial('image',0,20);
			var_dump($info);
		}  else {
			$wechatObj->text('hello 曹大猛')->reply();
		}
		break;
	case 'image':
		$imageInfo = $wechatObj->getRecPic();
		$wechatObj->image($imageInfo)->reply();
		break;
	case 'voice':
		$voiceInfo = $wechatObj->getRecVoice();
		$wechatObj->voice($voiceInfo)->reply();
		break;
	case 'shortvideo':
		$videoInfo = $wechatObj->getRecVideo();
		$videoInfo['title'] = '这是一段视频';
		$videoInfo['description'] = '家乐福及数量发生发送到了法律上多久了丰盛的是';
		$wechatObj->video($videoInfo)->reply();
		break;
	case 'uploadimg':

		break;
	default:
		# code...
		break;
}
// $wechatObj->valid();
