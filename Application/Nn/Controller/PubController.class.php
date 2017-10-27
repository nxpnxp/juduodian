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

}
