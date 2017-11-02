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
	
	public function yuelogs(){
    	
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$logs = M('WxuserYuelog')->where(array('uid'=>$user['id']))->select();
		$this->assign('logs',$logs);
		$this->display();
		
	}
	
	//我的收藏
	public function collection(){
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		$data = M('Collection')->where(array('uid'=>$user['id']))->select();
		$ids = '';
		foreach($data as $k=>$v){
			$ids .= $v['sid'].',';
		}
		$ids = trim($ids,',');
		$collections = M('Document')->where("id in ($ids)")->select();
		foreach($collections as $k=>$v){
			$collections[$k]['logo'] = M('Picture')->where(array('id'=>$v['cover_id']))->getField('path');
		}
		
		$this->assign('collections',$collections);
		$this->display();
	}
	
	public function yuetixian(){
    	
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$tixian_min_money = M('Config')->where('id=43')->getField('value');
		$this->assign('tixian_min_money',$tixian_min_money);
		
		//判断是否关注
		if($user['subscribe'] != '1'){
			$this->error('未关注，请关注后继续！');
		}
		//判断是否绑手机号
		if($user['isbind'] != '1'){
			$this->error('未绑手机号，请绑定后继续！');
		}
		
		$this->display();
	}
	
	public function doapply_tixian(){
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		
		$time = time();
		$_time = date('Y-m-d',$time);
		$time_min = strtotime($_time.' 00:00:00');
		$time_max = strtotime($_time.' 23:59:59');
		$search = M('WxuserTixian')
					->where('uid='.$user['id'].' and applytime>='.$time_min.' and applytime<'.$time_max)
					->find();
		if($search){
			if($search['type'] == '0'){
				$this->error('您今日已经申请提现，请耐心等待审核！');
			}
			if($search['type'] == '1'){
				$this->error('您今日申请提现已成功！');
			}
			if($search['type'] == '2'){
				$this->error('您今日申请提现被拒绝！');
			}
		}
		
		$fee = I('post.fee');
		$fee = intval($fee); 
		
		$tixian_min_money = M('Config')->where('id=43')->getField('value');
		if($fee < $tixian_min_money){
			$this->error('您申请的金额过小');
		}
		
		if($fee > $user['yue']){
			$this->error('您申请的金额超过您已有余额');
		}
		
		$ordersn = 'WXTX'.substr( md5('NNN'.time()) , 4,12);
		$data = array(
			'uid' => $user['id'],
			'applyfee' => $fee,
			'applytime' => $time,
			'type' => 0, //0已申请  1审核通过并打款  2拒绝  //改为无审核  直接打款
			'sn' => $ordersn
		);
		$res = M('WxuserTixian')->add($data);
		if($res){
			
			// 实例化支付接口
			$pay = & load_wechat('Pay');
			
			$amount = $fee * 100;
			$billno = $ordersn;
			$desc = '用户余额提现';
			
			// 调用方法
			$result = $pay->transfers($openid,$amount,$billno, $desc);
			
			// 处理结果
			if($result === FALSE){
				// 返回失败的处理结果
			    $err = $pay->errMsg;
				$this->error($err);
			}else{
				// 返回成功的处理结果
				M('WxuserTixian')->where('id='.$res)->save(array(
					'overfee' => $fee,
					'overtime' => time(),
					'type' => 1
				));
				M('WxuserCode')->where('id='.$user['id'])->setDec('yue',$fee);				
				M('WxuserYuelog')->add(array(
					'uid' => $user['id'],
					'fee' => $fee,
					'desc' => '余额提现：订单号['.$billno.'],金额['.$fee.']',
					'time' => time(),
					'oid' => '0-0'
				));		
				$this->success('提现成功');
			}
				
			
		}else{
			$this->error('提现失败');
		}
		
	}
	
}