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
		
		$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		echo $thisurl.'<hr/>empty';
		//$this->redirect('Index/index');
	}


    public function _initialize(){    	
		include "wechat/include.php";
		
		$pid = I('get.pid');
		if($pid){
			cookie('pid',$pid,3600); 
		}
		
		$openid = cookie('openid');
		if(!$openid){
			$this->get_user();
			
//			$openid = $this->openid;
//			if(!$openid){
//				$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
//				//echo $thisurl;die;
//				$this->get_user();
//			}
			
		}else{
			$this->openid = $openid; 
			$this->assign('openid',$openid);
			$this->user_pid($openid);
			$this->save_user_avatar($openid);
		}
		
    }
	
	//获取用户openid
	protected function get_user(){
		
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
			$this->save_user_avatar($openid);
	    	$this->get_user_info1($openid); 
			$this->assign('openid',$openid);
			$this->user_pid($openid);
				
		}else{
			$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
			$url = $oauth->getOauthRedirect($thisurl);			
			header('Location:'.$url);
		}
	}
//	
	//获取用户信息
	public function get_user_info1($openid){
		
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
//	
	//保存用户信息
	protected function save_gzh_info($info){
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
//
	//保存用户上下级关系
	protected function user_pid($openid){
		$user = M('WxuserCode')->where('openid="'.$openid.'"')->find();
		if($user){
			$pid = I('get.pid');
			if( empty($user['pid']) && !empty($pid) && ($pid!=$user['id']) ){
				M('WxuserCode')->where('id='.$user['id'])->save(array(
					'pid' => $pid
				));
			}
		}
	}
//	
	//保存用户头像
	protected function save_user_avatar($openid){
		$user = M('WxuserCode')->where('openid="'.$openid.'"')->find();
		if($user){
			$avatar = $user['headimgurl'];
			$isdlhimg = $user['isdlhimg'];
			if( !empty($avatar) && empty($isdlhimg) ){
				$data = file_get_contents($avatar);
				$filename = './Uploads/Avatar/'.$user['id'].'.png';
				@file_put_contents($filename, $data);
				if(file_exists($filename)){
					M('WxuserCode')->where('id='.$user['id'])->save(array(
						'isdlhimg' => 1
					));
				}
			}
		}
	}

	//发送模板消息
	public function sendmessage($openid='',$txt='hello'){
		if(!$openid){	
			$openid = $this->openid;
		}
		
		$data = array(
			'touser' => $openid,
			"msgtype" => "text",
			'text' => array(
				'content' => $txt
			)
		);
		$wechat = &load_wechat('Receive');
		$result = $wechat->sendCustomMessage($data);
		
		// 接口异常的处理
		if ($result === FALSE) {
		    echo $result->errMsg;
		    echo $result->errCode;
		} else {
		    // 接口正常的处理
		    //echo 'ook';
		}
	}

}
