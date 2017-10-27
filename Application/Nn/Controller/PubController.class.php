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
		
	}
	
	public function hbpay_success(){
		
		$openid = I('openid');
		$ordersn = I('ordersn');
		$total_fee = I('total_fee');
		$info = M('Wxhb')->where('sn="'.$ordersn.'"')->find();
		$data = array(
			'ispay' => 1,
			'paytype' => 1,
			'paymoney' => $total_fee/100
		);
		$rr = M('Wxhb')->where('id='.$info['id'])->save($data);
		
	}

}
