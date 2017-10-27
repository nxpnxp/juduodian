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
	$total_fee = $res['total_fee'];
	
	
	$data = array(
		'openid' => $openid,
		'ordersn' => $ordersn,
		'total_fee' => $total_fee
	);
	$url = 'http://'.$_SERVER['HTTP_HOST'].'/nn.php?s=/Pub/hbpay_success.html';
	_request($url,false,'post',$data);
		
}else{
	$back = 'FAIL';
	$msg = 'err';
}

$data = array(
	'return_code' => $back,
	'return_msg' => $msg
);
$pay->replyXml($data,true);

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
            'appid'           => 'wxaae12993a4d3e5f8', // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'       => '943bf485f4a4cdf32b27d7aa28829960', // 填写高级调用功能的密钥
            'encodingaeskey'  => 'gfiHc079up1aEoGI6Js2FRWanABLGUa9XCDZWo4HGBb', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
            'mch_id'          => '10018861', // 微信支付，商户ID（可选）
            'partnerkey'      => 'vipin521vipin521vipin521vipin905', // 微信支付，密钥（可选）
            'ssl_cer'         => '
MIIEZzCCA9CgAwIBAgICTJQwDQYJKoZIhvcNAQEFBQAwgYoxCzAJBgNVBAYTAkNO
MRIwEAYDVQQIEwlHdWFuZ2RvbmcxETAPBgNVBAcTCFNoZW56aGVuMRAwDgYDVQQK
EwdUZW5jZW50MQwwCgYDVQQLEwNXWEcxEzARBgNVBAMTCk1tcGF5bWNoQ0ExHzAd
BgkqhkiG9w0BCQEWEG1tcGF5bWNoQHRlbmNlbnQwHhcNMTQwOTMwMDkwMDQ0WhcN
MjQwOTI3MDkwMDQ0WjCBmDELMAkGA1UEBhMCQ04xEjAQBgNVBAgTCUd1YW5nZG9u
ZzERMA8GA1UEBxMIU2hlbnpoZW4xEDAOBgNVBAoTB1RlbmNlbnQxDjAMBgNVBAsT
BU1NUGF5MS0wKwYDVQQDFCTllK/lk4Hnp5HmioDvvIjlpKnmtKXvvInmnInpmZDl
hazlj7gxETAPBgNVBAQTCDEwMDE4ODYxMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A
MIIBCgKCAQEAtsjluphg9HeuaY2U//811103NfoQzZRhkIPkxyXAyvfTe32bfjlP
1lUlsitPNZpg+XZQMsZJidEsnZCnwid4XS7wy7E3ySS7wBVcp+I+N/shz+sjPCGB
lPbtvEQGnGs5+1T6We1SIOWB6rrET2TpQ6oTJfq8nshq+rc+mElJbd9tjN3v2qWH
vK9J6/V/XxKP5Eu9/CbwRbPePkWpxHZ6roD68CDHZWDMcI4zVNkKGdUkjfK8atwA
iTnqCfzTbKTaGtaCpCSF8unhTzev599bG4Ajr3wRt8OfmWttunIdBHhTZ1RmPiBg
zkGDXV9ji+yvpCn4XW0PvD2XKU1QOKV5xwIDAQABo4IBRjCCAUIwCQYDVR0TBAIw
ADAsBglghkgBhvhCAQ0EHxYdIkNFUy1DQSBHZW5lcmF0ZSBDZXJ0aWZpY2F0ZSIw
HQYDVR0OBBYEFHBk5AqB4tfNb9MYZHHQ7pFr/lZqMIG/BgNVHSMEgbcwgbSAFD4F
JvYiYrQVW4jNZH6w1GKn5YZ0oYGQpIGNMIGKMQswCQYDVQQGEwJDTjESMBAGA1UE
CBMJR3Vhbmdkb25nMREwDwYDVQQHEwhTaGVuemhlbjEQMA4GA1UEChMHVGVuY2Vu
dDEMMAoGA1UECxMDV1hHMRMwEQYDVQQDEwpNbXBheW1jaENBMR8wHQYJKoZIhvcN
AQkBFhBtbXBheW1jaEB0ZW5jZW50ggkAu1SXK7wA6FcwDgYDVR0PAQH/BAQDAgbA
MBYGA1UdJQEB/wQMMAoGCCsGAQUFBwMCMA0GCSqGSIb3DQEBBQUAA4GBAI5T4R60
HSliq0F5QHwKKT146o6lV45GQ33pxd/MbCwg9/PhhHtWLSr4I3OBU1JEu9GrZrqZ
NhzDm4OEtjHqmxytSIBeA/Z3xIUnSE4/qfpPwK6CCCqrMc0mjeG6nzQMskEckYvu
AYju3qQ71QhFq0sV98O3QHIK0/zywC//ynxr', // 微信支付，双向证书（可选，操作退款或打款时必需）
            'ssl_key'         => 'MIIEwAIBADANBgkqhkiG9w0BAQEFAASCBKowggSmAgEAAoIBAQC2yOW6mGD0d65p
jZT//zXXXTc1+hDNlGGQg+THJcDK99N7fZt+OU/WVSWyK081mmD5dlAyxkmJ0Syd
kKfCJ3hdLvDLsTfJJLvAFVyn4j43+yHP6yM8IYGU9u28RAacazn7VPpZ7VIg5YHq
usRPZOlDqhMl+ryeyGr6tz6YSUlt322M3e/apYe8r0nr9X9fEo/kS738JvBFs94+
RanEdnqugPrwIMdlYMxwjjNU2QoZ1SSN8rxq3ACJOeoJ/NNspNoa1oKkJIXy6eFP
N6/n31sbgCOvfBG3w5+Za226ch0EeFNnVGY+IGDOQYNdX2OL7K+kKfhdbQ+8PZcp
TVA4pXnHAgMBAAECggEBAKOdoUYuMFuk5hLGEaI1qNRnNIy0k2XydIMQDTHWsYT9
4eigvxd15elNvriF3Bl3X2buaUuKAznCa9V/Lyu0eSGwawtqTy1WHFoMxSvicR9b
RSEAskGZHI1jm9ryaZiKwxQfNRpaPTIFPw4GsMMA6W0QSKOuljjr3hcfAIEA2SHn
YkScdjVCFziZCC8p8u0xVZIRPVcl0KrzUmPdZ08+PsYIU+5B982EIPcHSKP16lQC
BiCZnjitFDOvSOGZzMRh4vKq0j/C3RJsH/ck/6Mg3dRAZKV/CYFOk4F8aRrn/FyT
JmkMtD5yuzvXy0avMSSHVBnkhE1bOMukx969z9+5WkECgYEA4zOg/bV7aq2TgVx9
kvv3vVyNCqeVv71G9ANtHLGQzlamcmw1+ZBv447Ng8wX1YB3VMKpH4IN31dtW0NZ
2cxlIJ7VYpM2EHEDMvoiTOfsCwCj4WVkBvr5q+xJXVEOyKgMZ3ZWeUCvfMpdxH9x
+JjaM6TbH3a5jhXzbngrrZtOQuUCgYEAzfQArYDWsT48oYeIz5/dnKXypnJVCg7Z
W+//kT4pg2jBHBbCvCtT7EoQkBDF5BYwI6NZ0Q5R3Hyq0Gd1Wp2i8ZDkBfESFOvi
ezV3vEQPGP5Ym1sUT5RuiOJfST/N/B8wvCpNOD5hGo1I66n4NLeMJTQ62fC4/tge
J/p0NHtv4zsCgYEAtokrRcqBrlJoGN1jMiznfCaYVkAP6q06DR+XkR8D1RL+xR01
PB0UF8IKUWIun+SHevyt/ddyJ8bI4DK7RGWXtd6GV50oKYtyY4lc92a5WJmaEKNx
+r22y66ZvrQ56XJCDPmhPed/VgYL0awGuBCt9iHzvlKXf5UZL96yUNdeyBUCgYEA
tSiPPA7bSlbzYCZm+jKl7bevBCgHk2xSV2N5SxoBzBxl2L5QltIJ5QuVHBQU7bdy
qrZyN8i/yxFB80U93fJRNOl9GZjejdabhkMWylyXZyW7bRQlfoaS4Ac3R6g/GSma
ZblT6Ug9g+yJGvqMYFqfhM43giMTpt8VT5cnhk6ZsacCgYEAz6q9OqABksNCB27G
iJZdk5DWjmlkOTCwjThBguWRQdwIuhncoS2EjZGM0PVg2172CfFPrHxC2/jVfXJ/
1b5nF0P6mzg+ZLcAvOlYVAIuQmLojnDjth5RUj8nVEa2Q07Q8SThRzolydbUPPAm
idouN1lzjj8ejN/4poN1frWJdjc=
            ', // 微信支付，双向证书（可选，操作退款或打款时必需）
            'cachepath'       => '', // 设置SDK缓存目录（可选，默认位置在Wechat/Cache下，请保证写权限）
        );
        \Wechat\Loader::config($options);
        $wechat[$index] = \Wechat\Loader::get($type);
    }
    return $wechat[$index];
}
?>