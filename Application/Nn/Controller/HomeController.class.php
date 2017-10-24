<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Nn\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {
	
	protected $openid;
	
	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}


    protected function _initialize(){
		include "wechat/include.php";
		
		
		$openid = cookie('openid');
		if(!$openid){
			$this->get_user();
		}else{
			$this->openid = $openid; 
		}
		
    	
    }
	
	//获取用户openid
	private function get_user(){
		
		$oauth = & load_wechat('Oauth');
		$code = I('get.code');
		if($code){
			$info = $oauth->getOauthAccessToken();
			$openid = (string)$info['openid'];			
			
			//作为子控制器使用
			$this->openid = $openid; 
			//作为判断是否存在缓存，存在则不获取.
			cookie('openid',$openid,3600); 
				
	    	//获取用户信息
	    	$this->get_user_info($openid); 
				
		}else{
			$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
			$url = $oauth->getOauthRedirect($thisurl);
			header('Location:'.$url);
		}
	}
	
	//获取用户信息
	private function get_user_info($openid){
		
		$user = & load_wechat('User');
		$result = $user->getUserInfo($openid);
		if ($result === FALSE) {
		    echo $user->errMsg;
		    echo $user->errCode;
		    exit;
		} else {
			//保存用户信息
		    $this->save_gzh_info($result);
		}
		
	}
	
	//保存用户信息
	private function save_gzh_info($info){
		if(!is_array($info)){ die('Error: 0001'); }
		
		$openid = $info['openid'];
		$subscribe = $info['subscribe'];//是否关注
		$time = time();
		
		$search = M('WxuserCode')->where(array('openid'=>$openid))->find();
		if($search){
			
			if($subscribe && !$search['subscribe']){
				//原是未关注，更新已关注用户信息
				$array = array();
				$array['subscribe'] = $subscribe;
				$array['gzh_info'] = serialize($info);
				$array['nickname'] = $info['nickname'];
				$array['sex'] = $info['sex'];
				$array['country'] = $info['country'];
				$array['province'] = $info['province'];
				$array['city'] = $info['city'];
				$array['headimgurl'] = $info['headimgurl'];		
				M('WxuserCode')->where(array('id'=>$search['id']))->save($array);
			}
			
		}else{
			
			$array = array();
			$array['openid'] = $openid;		
			$array['subscribe'] = $subscribe;
			$array['time'] = $time;
			if($array['subscribe']){
				$array['gzh_info'] = serialize($info);
				$array['nickname'] = $info['nickname'];
				$array['sex'] = $info['sex'];
				$array['country'] = $info['country'];
				$array['province'] = $info['province'];
				$array['city'] = $info['city'];
				$array['headimgurl'] = $info['headimgurl'];			
			}
			M('WxuserCode')->add($array);
			
		}
	}


}
