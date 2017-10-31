<?php

namespace Nn\Controller;

class MemberController extends HomeController {

    public function index(){
    	
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
    	$this->display();
	}
			
	private function _request($url, $https=false, $method='get', $data=null)
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
	
	public function savelatlon(){
    	
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		
		$lat = I('post.lat');
		$lon = I('post.lon');
		M('WxuserLatlon')->add(array(
			'uid' => $user['id'],
			'lat' => $lat,
			'lon' => $lon,
			'time' => time()
		));
		
	}
	
	
	
}