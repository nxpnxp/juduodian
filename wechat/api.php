<?php

require 'include.php';

$config = array(    
    'appid' 			=> 'wxaae12993a4d3e5f8',
    'appsecret'       => '943bf485f4a4cdf32b27d7aa28829960',
    'token' 			=> 'FU5zfr54p11EpzyRP1RZVuFypYN1yaPe',
	'encodingaeskey'  => 'gfiHc079up1aEoGI6Js2FRWanABLGUa9XCDZWo4HGBb'
);

$wechat = &\Wechat\Loader::get('Receive', $config);

/* 验证接口 */
if ($wechat->valid() === FALSE) {
	// 接口验证错误，记录错误日志
     // log_message('ERROR', "微信被动接口验证失败，{$wechat->errMsg}[{$wechat->errCode}]"); 
     \Wechat\Lib\Tools::log("API Faild . {$wechat->errMsg} [$wechat->errCode]", 'Err');
            
     // 退出程序
     exit($wechat->errMsg);
 }

/* 获取粉丝的openid */
$openid = $wechat->getRev()->getRevFrom();

/* 记录接口日志，具体方法根据实际需要去完善 */
// _logs();
//\Wechat\Lib\Tools::log( json_encode($wechat->getRev()) , 'Err1');  
  

switch ($wechat->getRev()->getRevType()) {
	
    case \Wechat\WechatReceive::MSGTYPE_EVENT:
           	$event = $wechat->getRevEvent();
		 	
			$type = strtolower($event['event']);
		   	if($type == 'unsubscribe'){
		   		$data = array(
					'openid' => $openid
				);
				$url = 'http://jdd.vipin.net.cn/nn.php?s=/index/unsubscribe.html';
		   		_request($url, false, 'post', $data);
		   		exit("success");
		   	}elseif($type == 'subscribe'){
		   		$data = array(
					'openid' => $openid
				);
				$url = 'http://jdd.vipin.net.cn/nn.php?s=/index/subscribe.html';
		   		_request($url, false, 'post', $data);
		   		exit("success");
		   	}
        
    default:
          
}


function _request($url, $https=false, $method='get', $data=null)
{
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url); //设置URL
	curl_setopt($ch,CURLOPT_HEADER,false); //不返回网页URL的头信息
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);//不直接输出返回一个字符串
	if($https){
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//服务器端的证书不验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//客户端证书不验证
	}
	if($method == 'post'){
		curl_setopt($ch, CURLOPT_POST, true); //设置为POST提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交数据$data
	}
	$str = curl_exec($ch);//执行访问
	curl_close($ch);//关闭curl释放资源
	return $str;
}
