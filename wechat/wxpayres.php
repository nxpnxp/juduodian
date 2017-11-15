<?php
include "include.php";
$pay = & load_wechat('Pay');		
$res = $pay->getNotify();
//$err = $pay->errMsg;
//file_put_contents('abc.txt', json_encode($res));
if($res !== FALSE){
	$back = 'SUCCESS';
	$msg = 'ok';
	
	$openid = $res['openid'];
	$ordersn = $res['out_trade_no'];
	
	$data = array(
		'openid' => $openid,
		'ordersn' => $ordersn
	);
	$url = 'http://'.$_SERVER['HTTP_HOST'].'/nn.php?s=/Pub/pay_success.html';
	_request($url,false,'post',$data);
	//header('Location:'.$url);
		
}else{
	$back = 'FAIL';
	$msg = 'err';
}

$data = array(
	'return_code' => $back,
	'return_msg' => $msg
);
//$pay->replyXml($data,true);
$pay->replyXml($data);

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

/**
 * 获取微信操作对象（单例模式）
 * @staticvar array $wechat 静态对象缓存对象
 * @param type $type 接口名称 ( Card|Custom|Device|Extend|Media|Oauth|Pay|Receive|Script|User ) 
 * @return \Wehcat\WechatReceive 返回接口对接
 */
function & load_wechat($type = '') {
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
    	// 定义微信公众号配置参数（这里是可以从数据库读取的哦）
       $options = array(      	
        	'token'           => 'FU5zfr54p11EpzyRP1RZVuFypYN1yaPe', // 填写你设定的key
            'appid'           => 'wx08345ce6da3cdfb2', // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'       => '23caaf0913a9e6d6e1b40a8beda98764', // 填写高级调用功能的密钥
            'encodingaeskey'  => 'gfiHc079up1aEoGI6Js2FRWanABLGUa9XCDZWo4HGBb', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
            'mch_id'          => '10052996', // 微信支付，商户ID（可选）
            'partnerkey'      => 'ewy7w6e5y484yh15f4gwey87h49r8y98', // 微信支付，密钥（可选）
            'ssl_cer' => './wechat/cert/apiclient_cert1.pem',
            'ssl_key' => './wechat/cert/apiclient_key1.pem',
            'cachepath'       => '', // 设置SDK缓存目录（可选，默认位置在Wechat/Cache下，请保证写权限）
       
		
		);
        \Wechat\Loader::config($options);
        $wechat[$index] = \Wechat\Loader::get($type);
    }
    return $wechat[$index];
}
?>