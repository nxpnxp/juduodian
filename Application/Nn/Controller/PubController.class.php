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

}
