<?php

namespace Nn\Controller;
use Think\Controller;

class PubController extends Controller {
	
	public function pay_success(){
		
		$openid = I('openid');
		$ordersn = I('ordersn');
		
		$info = M('DocumentShop')->where('ordersn="'.$ordersn.'"')->find();
		$data = array(
			'paytype' => 1,
			'paystatus' => 1
		);
		$rr = M('DocumentShop')->where('id='.$info['id'])->save($data);
		
		$document = M('Document')->where('id='.$info['id'])->find();
		$data1 = array(
			'status' => 1//状态 -1回收站 0禁用 1可用 2待审核
		);
		$rr = M('Document')->where('id='.$document['id'])->save($data1);
		
		//如果该店是通过谁来的  给佣金
		if($info['ppid']>0){
			$paymoney = M('Config')->where(array('name'=>"NXP_APPLY_DIAN_PAY"))->getField('value');
			$yj = M('Config')->where(array('id'=>"45"))->getField('value');
			if($paymoney && $yj){
				
				$money = $paymoney * $yj / 100;
				$money = sprintf("%.2f",substr(sprintf("%.3f", $money), 0, -2));
				
				//增加余额			
				M('WxuserCode')->where('id='.$info['ppid'])->setInc('yue',$money);
				
				//存余额记录
				M('WxuserYuelog')->add(array(
					'uid' => $info['ppid'],
					'fee' => $money,
					'desc' => '店铺['.$info['id'].']开店成功，获得佣金['.$money.']'.'转入余额',
					'time' => time(),
					'oid' => 'kdyj'
				));
				
			}
		}
		
		//创建店铺完成  发模板消息 提醒发红包
		$this->sendmessage('','恭喜您创建成功，给店铺增加红包试试，会有意想不到的效果！~');
		
		echo 'success';
	}
	
	public function hbpay_success(){
		
		$openid = I('openid');
		$ordersn = I('ordersn');
		$total_fee = I('total_fee');
		$info = M('Wxhb')->where('sn="'.$ordersn.'"')->find();
		if(!$info){ return false; }
		
		$data = array(
			'ispay' => 1,
			'paytype' => 1,
			'paymoney' => $total_fee/100
		);
		$rr = M('Wxhb')->where('id='.$info['id'])->save($data);
		
		//天数大于1的  按 24小时计算  
		//天数等于1的  按后台设置的期限计算
		if($info['num'] > 1){
			
			if($info['isson'] == '0'){			
				//生成子红包数据
				$gettime = $info['gettime'];
				$endtime = $gettime + 24*3600;
				for ($i=0; $i < $info['num']; $i++) { 
					$data = array(
						'hbid' => $info['id'],
						'shopid' => $info['shopid'],
						'everymoney' => $info['everymoney'],
						'yue' => $info['everymoney'],
						'type' => $info['type'],
						'ptmoney' => $info['ptmoney'],
						'psqmoney1' => $info['psqmoney1'],
						'psqmoney2' => $info['psqmoney2'],
						'iskl' => $info['iskl'],
						'kl' => $info['kl'],
						'gettime' => $gettime,
						'endtime' => $endtime,
						'area' => $info['area']
					);
					M('WxhbSon')->add($data);
					$gettime = $endtime;
					$endtime = $gettime + 24*3600;
				}
				
				//放置重复插入
				M('Wxhb')->where('id='.$info['id'])->save(array('isson'=>1));
				
			}
			
		}
		
		echo 'success';
		
		
	}

	//发送模板消息
	private function sendmessage($openid='',$txt='hello'){
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
