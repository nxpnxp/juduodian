<?php

namespace Nn\Controller;

class ShopController extends HomeController {

	/**
	 * 创建店铺
	 */
    public function index(){
    	
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		
		if($user['isbind'] == '0'){
			$this->redirect('Index/bind', array(), 0, '页面跳转中...');die();
		}
		
		$cate1 = M('Category')->field('id,title')->where(array('pid'=>'0'))->order('sort asc')->select();
		$this->assign('cate1',$cate1);
			
		$dian = M('Document')->alias('d')
				->join('left join onethink_document_shop ds on d.id=ds.id')
				->where('d.uid='.$user['id'].' and ds.paystatus=0')->find();
				
				
		if($dian){
			//申请过
			$dian_shop = M('DocumentShop')->find($dian['id']);
			if($dian_shop['paystatus']){
				//已支付
				$this->display('waitsh');
			}else{
				//未支付,可修改信息
				$script = &  load_wechat('Script');
				$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
				$options = $script->getJsSign($thisurl);
				$options = json_encode($options);
							
				if($options===FALSE){
				    echo $script->errMsg;die;
				}else{
					$this->assign('options',$options);
					
					//获取原信息 进行修改
					$this->assign('dian',$dian);
					$this->assign('dian_shop',$dian_shop);
					//---
					$cover_id = $dian['cover_id'];
					$imgs_id  = $dian_shop['imgs'];
					$imgs_id1 = $dian_shop['imgs1'];
					$imgs_id2 = $dian_shop['imgs2'];
					$imgs_id3 = $dian_shop['imgs3'];
					$imgs_id4 = $dian_shop['imgs4'];
					$imgs_id5 = $dian_shop['imgs5'];
					if($cover_id > 0){ //logo
						$cover = M('Picture')->find($cover_id);
						$cover_src = $cover['path'];
						$this->assign('cover_src',$cover_src);
					}
					if($imgs_id > 0){ //封面1
						$imgs = M('Picture')->find($imgs_id);
						$imgs_src = $imgs['path'];
						$this->assign('imgs_src',$imgs_src);
					}
					if($imgs_id1 > 0){ //封面2
						$imgs1 = M('Picture')->find($imgs_id1);
						$imgs_src1 = $imgs1['path'];
						$this->assign('imgs_src1',$imgs_src1);
					}
					if($imgs_id2 > 0){ //封面3
						$imgs2 = M('Picture')->find($imgs_id2);
						$imgs_src2 = $imgs2['path'];
						$this->assign('imgs_src2',$imgs_src2);
					}
					if($imgs_id3 > 0){ //详情1
						$imgs3 = M('Picture')->find($imgs_id3);
						$imgs_src3 = $imgs3['path'];
						$this->assign('imgs_src3',$imgs_src3);
					}
					if($imgs_id4 > 0){ //详情2
						$imgs4 = M('Picture')->find($imgs_id4);
						$imgs_src4 = $imgs4['path'];
						$this->assign('imgs_src4',$imgs_src4);
					}
					if($imgs_id5 > 0){ //二维码
						$imgs5 = M('Picture')->find($imgs_id5);
						$imgs_src5 = $imgs5['path'];
						$this->assign('imgs_src5',$imgs_src5);
					}
					//---
					$category_id = $dian['category_id'];
					$cate1_id = M('Category')->where('id='.$category_id)->getField('pid');
					$this->assign('cate1_id',$cate1_id);
					$cate2 = M('Category')->field('id,title')->where(array('pid'=>$cate1_id))->order('sort asc')->select();
					$this->assign('cate2',$cate2);
					
					$this->display('apply_edit');
				}
			}
		}else{
			//没有申请过
			$script = &  load_wechat('Script');
			$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
			$options = $script->getJsSign($thisurl);
			$options = json_encode($options);
						
			if($options===FALSE){
			    echo $script->errMsg;die;
			}else{
				$this->assign('options',$options);
				$this->display('apply');
			}
		}
		
    	
	}
	
	//编辑店铺
	public function editshop(){
		
		$id = I('id');
		$one = M("Document")->where(array('id'=>$id))->find();
		$dian = M('DocumentShop')->where(array('id'=>$id))->find();
		if($dian['paystatus']==0){
			$this->error("您的店铺已创建，请去支付",U('apply_success',array('ordersn'=>$dian['ordersn'])));
			exit;
		}
		
		$script = &  load_wechat('Script');
		$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$options = $script->getJsSign($thisurl);
		$options = json_encode($options);
		if($options===FALSE){
			    echo $script->errMsg;die;
			}else{
				$this->assign('options',$options);
			}
			
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$one['logo'] = M("Picture")->where(array('id'=>$one['cover_id']))->getField('path');
		
		$this->assign('dian',$dian);
		
		$cate1 = M('Category')->field('id,title')->where(array('pid'=>'0'))->order('sort asc')->select();
		$this->assign('cate1',$cate1);
		$category_id = $one['category_id'];
		$cate1_id = M('Category')->where('id='.$category_id)->getField('pid');
		$this->assign('cate1_id',$cate1_id);
		$cate2 = M('Category')->field('id,title')->where(array('pid'=>$cate1_id))->order('sort asc')->select();
		$this->assign('cate2',$cate2);
		
		
		$imgs_id  = $dian['imgs'];
		$imgs_id1 = $dian['imgs1'];
		$imgs_id2 = $dian['imgs2'];
		$imgs_id3 = $dian['imgs3'];
		$imgs_id4 = $dian['imgs4'];
		$imgs_id5 = $dian['imgs5'];

		if($imgs_id > 0){ //封面1
			$imgs = M('Picture')->find($imgs_id);
			$one['img'] = $imgs['path'];
		}
		if($imgs_id1 > 0){ //封面2
			$imgs1 = M('Picture')->find($imgs_id1);
			$one['img1'] = $imgs1['path'];
		}
		if($imgs_id2 > 0){ //封面3
			$imgs2 = M('Picture')->find($imgs_id2);
			$one['img2'] = $imgs2['path'];
		}
		if($imgs_id3 > 0){ //详情1
			$imgs3 = M('Picture')->find($imgs_id3);
			$one['img3'] = $imgs3['path'];
		}
		if($imgs_id4 > 0){ //详情2
			$imgs4 = M('Picture')->find($imgs_id4);
			$one['img4'] = $imgs4['path'];
		}
		if($imgs_id5 > 0){ //二维码
			$imgs5 = M('Picture')->find($imgs_id5);
			$one['img5'] = $imgs5['path'];
		}
		$this->assign('one',$one);
		//print_r($one);
		$this->display();
		
	}
	
	public function doapplyedit1(){
    	$time = time();
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$id = I('post.id');
		
		$array1 = array(
			'uid' => $user['id'],
			'title' => I('post.title'),
			'category_id' => I('post.cate2'),
			'description' => I('post.brief'),
			'model_id' => 4, //模型id
			'type' => 1, //1目录   （2主题 3段落）
			'cover_id' => I('post.newid2'), //店铺logo
			'display' => 1,//所有人可见
			'deadline' => 0,//截止时间
			'create_time' => $time,
			'update_time' => $time,
			'status' => 1//状态 -1回收站 0禁用 1可用 2待审核
 		);
		M('Document')->where('id='.$id)->save($array1);
		
		$lnglat = I('post.lnglat');
		$lnglat = explode(',', $lnglat);
		$ordersn = 'WXAPPLY'.substr( md5('NNN'.time()) , 4,12);
		
		$array2 = array(
			'imgs' => I('post.img1'),  //店铺形象图1
			'imgs1' => I('post.img2'), //店铺形象图2
			'imgs2' => I('post.img3'), //店铺形象图3
			'imgs3' => I('post.img5'), //详情图1
			'imgs4' => I('post.img6'), //详情图2
			'imgs5' => I('post.img7'), //二维码
			'address' => I('post.address'),
			'showaddress' => I('post.showaddress'),
			'mobile' => I('post.mobile'),
			'content' => I('post.content'),
			'longitude' => $lnglat[0], 
			'latitude' => $lnglat[1],
			'ordersn' => $ordersn,
		);
		$id2 = M('DocumentShop')->where('id='.$id)->save($array2);
		
		if($id && $id2){
			$this->success('修改成功', U('shop/dians'));
		}else{
			$this->error('修改失败');
		}
		
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
	
	
	public function doapply(){
    	$time = time();
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		
		$array1 = array(
			'uid' => $user['id'],
			'title' => I('post.title'),
			'category_id' => I('post.cate2'),
			'description' => I('post.brief'),
			'model_id' => 4, //模型id
			'type' => 1, //1目录   （2主题 3段落）
			'cover_id' => I('post.newid2'), //店铺logo
			'display' => 1,//所有人可见
			'deadline' => 0,//截止时间
			'create_time' => $time,
			'update_time' => $time,
			'status' => 2//状态 -1回收站 0禁用 1可用 2待审核
 		);
		$id = M('Document')->add($array1);
		
		$lnglat = I('post.lnglat');
		$lnglat = explode(',', $lnglat);
		$ordersn = 'WXAPPLY'.substr( md5('NNN'.time()) , 4,12);
		
		$pid = cookie('pid');
		if(!$pid){ $pid=0; }
		
		$array2 = array(
			'id' => $id,
			'imgs' => I('post.img1'),  //店铺形象图1
			'imgs1' => I('post.img2'), //店铺形象图2
			'imgs2' => I('post.img3'), //店铺形象图3
			'imgs3' => I('post.img5'), //详情图1
			'imgs4' => I('post.img6'), //详情图2
			'imgs5' => I('post.img7'), //二维码
			'address' => I('post.address'),
			'showaddress' => I('post.showaddress'),
			'mobile' => I('post.mobile'),
			'content' => I('post.content'),
			'longitude' => $lnglat[0], 
			'latitude' => $lnglat[1],
			'ordersn' => $ordersn,
			'paytype' => 0,
			'paystatus' => 0,
			'ppid' => $pid
		);
		$id2 = M('DocumentShop')->add($array2);
		
		if($id && $id2){
			$this->success('申请成功', U('apply_success',array('ordersn'=>$ordersn)) );
		}else{
			$this->error('申请失败');
		}
		
	}

	public function apply_success(){
		
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$ordersn = I('get.ordersn');
		$this->assign('ordersn',$ordersn);
		$pay_price = $this->getpaymoney();
		$this->assign('pay_price',$pay_price);
		
		$this->display();
	}
	
	private function getpaymoney(){
		$res = M('Config')->where(array('name'=>"NXP_APPLY_DIAN_PAY"))->find();
		if(!$res){
			die('查询支付金额失败！');
		}
		return $res['value'];
	}
	
	public function pay(){
		
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$ordersn = I('get.ordersn');
		$price = I('get.price');
		$type = I('get.type');
		
		//验证 （订单号 + 金额）
		$pay_price = $this->getpaymoney();
		if($pay_price != $price){
			die('支付金额有误！');
		}
		$info = M('DocumentShop')->where('ordersn="'.$ordersn.'"')->find();
		if(!$info){
			die('请重新申请！');
		}else{
			if( empty($info['imgs']) || empty($info['address']) || empty($info['mobile']) || empty($info['longitude']) || empty($info['latitude']) ){
				die('请重新申请！');
			}
		}
		
		$body = '支付';
		$out_trade_no = $ordersn;
		$total_fee = $price * 100;
		$notify_url = 'http://'.$_SERVER['HTTP_HOST'].'/wechat/wxpayres.php';
		
		//$notify_url = 'http://'.$_SERVER['HTTP_HOST'].U('Pub/pay_success');
		//echo $notify_url;die;
		//header('Location:'.$notify_url);die;
		
		if($type == '1'){
			//微信支付

			$pay = & load_wechat('Pay');
			$prepayid = $pay->getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI");
			if($prepayid===FALSE){
				header("Content-type:text/html;charset=utf-8");
			    echo '1--'.$pay->errMsg;die;
			}
			$pay_options = $pay->createMchPay($prepayid);			
			if($pay_options===FALSE){
				header("Content-type:text/html;charset=utf-8");
			    echo '2--'.$pay->errMsg;die;
			}
			$pay_options = json_encode($pay_options);
			$this->assign('pay_options',$pay_options);	
			
			$script = &  load_wechat('Script');
			$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
			$options = $script->getJsSign($thisurl);
			$options = json_encode($options);	
			if($options===FALSE){
				header("Content-type:text/html;charset=utf-8");
			    echo '3--'.$script->errMsg;die;
			}
			$this->assign('options',$options);	
						
			$this->display('wxpay');

		}elseif($type == '2'){
			//余额支付
			$useryue = $user['yue'];
			$waitpay = $price;
			if($useryue < $waitpay){
				$this->error('抱歉，您的余额不足！');
			}
			
			//扣除余额
			M('WxuserCode')->where('id='.$user['id'])->setDec('yue',$waitpay);
			//存余额记录
			M('WxuserYuelog')->add(array(
				'uid' => $user['id'],
				'fee' => '-'.$waitpay,
				'desc' => '开店成功，支付余额['.$waitpay.']',
				'time' => time(),
				'oid' => '0-0'
			));
			//完成支付后逻辑		
			$data = array(
				'openid' => $openid,
				'ordersn' => $ordersn
			);
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/nn.php?s=/Pub/pay_success.html';
			$this->_request($url,false,'post',$data);
			$this->success('恭喜您，开店成功！',U('dians'));
		}

	}

	//支付完成 等待审核
	public function waitsh(){
		$this->display();
	}
	
	public function doapplyedit(){
    	$time = time();
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		
		$id = I('post.id');
		
		$array1 = array(
			'uid' => $user['id'],
			'title' => I('post.title'),
			'category_id' => I('post.cate2'),
			'description' => I('post.brief'),
			'model_id' => 4, //模型id
			'type' => 1, //1目录   （2主题 3段落）
			'cover_id' => I('post.newid2'), //店铺logo
			'display' => 1,//所有人可见
			'deadline' => 0,//截止时间
			'create_time' => $time,
			'update_time' => $time,
			'status' => 2//状态 -1回收站 0禁用 1可用 2待审核
 		);
		M('Document')->where('id='.$id)->save($array1);
		
		$lnglat = I('post.lnglat');
		$lnglat = explode(',', $lnglat);
		$ordersn = 'WXAPPLY'.substr( md5('NNN'.time()) , 4,12);
		
		$array2 = array(
			'imgs' => I('post.img1'),  //店铺形象图1
			'imgs1' => I('post.img2'), //店铺形象图2
			'imgs2' => I('post.img3'), //店铺形象图3
			'imgs3' => I('post.img5'), //详情图1
			'imgs4' => I('post.img6'), //详情图2
			'imgs5' => I('post.img7'), //二维码
			'address' => I('post.address'),
			'showaddress' => I('post.showaddress'),
			'mobile' => I('post.mobile'),
			'content' => I('post.content'),
			'longitude' => $lnglat[0], 
			'latitude' => $lnglat[1],
			'ordersn' => $ordersn,
			'paytype' => 0,
			'paystatus' => 0
		);
		$id2 = M('DocumentShop')->where('id='.$id)->save($array2);
		
		if($id && $id2){
			$this->success('申请成功', U('apply_success',array('ordersn'=>$ordersn)) );
		}else{
			$this->error('申请失败');
		}
		
	}

	/**
	 * 我的店铺列表
	 */
    public function dians(){
    	$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$dians = M('Document')->alias('d')
				->join('left join onethink_document_shop ds on d.id=ds.id')
				->where('d.uid='.$user['id'])->select();
		$daybegin=strtotime(date("Ymd")); 
		$dayend=$daybegin+86400;
		foreach($dians as $k=>$v){
			$dians[$k]['logo'] = M('Picture')->where(array('id'=>$v['cover_id']))->getField('path');
			$dians[$k]['collection'] = M("Collection")->where(array('sid'=>$v['id']))->count();
			$dians[$k]['zan'] = M("Zan")->where(array('sid'=>$v['id']))->count();
			$flag = 0;
			$flag = M("Wxhb")->where("shopid={$v['id']} and $daybegin>gettime and $dayend < endtime")->count();
			if($flag <=0){
				$flag = M("Wxhb")->where("shopid={$v['id']} and $daybegin>gettime and $dayend < endtime")->count();
			}
			$collections[$k]['hb'] = $flag;
		}
		//print_r($dians);
		$this->assign('dians',$dians);
    	$this->display('index');
    }
	
	/**
	 * 店铺详情
	 */
    public function detail(){
    	$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$id = I('get.id');
		
		//更新店铺红包是否过期
		$this->update_shophb($id);
		
		$dian = M('Document')->alias('d')
				->field("d.*,ds.*,p.path imgsp,p1.path imgs1p,p2.path imgs2p,p3.path imgs3p,p4.path imgs4p,p5.path imgs5p")
				->join('left join onethink_document_shop ds on d.id=ds.id')
				->join('left join onethink_picture p on ds.imgs=p.id')
				->join('left join onethink_picture p1 on ds.imgs1=p1.id')
				->join('left join onethink_picture p2 on ds.imgs2=p2.id')
				->join('left join onethink_picture p3 on ds.imgs3=p3.id')
				->join('left join onethink_picture p4 on ds.imgs4=p4.id')
				->join('left join onethink_picture p5 on ds.imgs5=p5.id')
				->where('d.id='.$id)->find();
				
				
		$dian['zan'] = M("Zan")->where(array('sid'=>$dian['id']))->count();
		
		$this->assign('dian',$dian);
		
		$wait = M('Config')->where(array('id'=>"44"))->getField('value');
		if(!$wait){
			$wait = 8;
		}
		
		$flag = 0;//0没红包 1未开始 2疯抢中 3已结束 4不可抢
		$time = time();
		$wxhb = M('Wxhb')->where('shopid='.$id.' and ispay=1 and yue>0')->find();
		if($wxhb){
			if($wxhb['isson'] && ($wxhb['num']>1)){
				//多日
				$wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 and gettime<='.$time.' and endtime>='.$time)->find();
				if($wxhbson){
					$this->assign('iskl',$wxhbson['iskl']);
					$this->assign('kl',$wxhbson['kl']);
					$flag = 2;//2疯抢中
				}else{
					$flag = 4;//4不可抢
					$_wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 ')->find();
					if($time < $_wxhbson['gettime']){
						$this->assign('gettime',date('Y-m-d H:i:s',$_wxhbson['gettime']));
						$flag = 1;//1未开始
					}
					if( ($time >= $_wxhbson['gettime']) && ($time <= $_wxhbson['endtime']) ){
						$this->assign('iskl',$_wxhbson['iskl']);
						$this->assign('kl',$_wxhbson['kl']);
						$flag = 2;//2疯抢中
					}
					if($time > $_wxhbson['endtime']){
						$flag = 3;//3已结束
					}
				}
			}
			if(($wxhb['isson']==0) && ($wxhb['num']==1)){
				//单日
				if($time < $wxhb['gettime']){
					$this->assign('gettime',date('Y-m-d H:i:s',$wxhb['gettime']));
					$flag = 1;//1未开始
				}
				if( ($time >= $wxhb['gettime']) && ($time <= $wxhb['endtime']) ){
					$this->assign('iskl',$wxhb['iskl']);
					$this->assign('iskl',$wxhb['kl']);
					$flag = 2;//2疯抢中
				}
				if($time > $wxhb['endtime']){
					$flag = 3;//3已结束
				}
			}
		}
		$this->assign('wait',$wait);
		$this->assign('flag',$flag);
		
		if($flag == '2'){ //可抢		
			//8秒后 显示  判断结果
			$flag2 = 1;
			$flag2_msg = '';
			
			$flag2_res = $this->check_user_can_qhb($dian['id']);
			$flag2 = $flag2_res['i'];
			$flag2_msg = $flag2_res['msg'];
			
			$this->assign('flag2',$flag2);
			$this->assign('flag2_msg',$flag2_msg);
		}
		
		$logo = M('Picture')->where(array('id'=>$dian['cover_id']))->getField('path');
		$this->assign('logo',$logo);
    	$this->display();
    }

	private function update_shophb($id){
		$time = time();
		$wxhb = M('Wxhb')->where('shopid='.$id.' and ispay=1 and yue>0')->find();
		if($wxhb){
			if($wxhb['isson'] && ($wxhb['num']>1)){
				//多日
				$wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 and endtime<'.$time)->find();
				if($wxhbson){
					
					//清除红包剩余余额
					$yue = $wxhbson['yue'];
					M('WxhbSon')->where('id='.$wxhbson['id'])->save(array('yue'=>0));
					M('Wxhb')->where('id='.$wxhb['id'])->setDec('yue',$yue);
					
					//剩余余额返还商户余额
					$shopid = $wxhbson['shopid'];
					$uid = M('Document')->where(array('id'=>$shopid))->getField('uid');					
					$shopname = M('Document')->where(array('id'=>$shopid))->getField('title');
					M('WxuserCode')->where('id='.$uid)->setInc('yue',$yue);
					
					//商户余额记录
					M('WxuserYuelog')->add(array(
						'uid' => $uid,
						'fee' => $yue,
						'desc' => '红包店['.$shopname.']剩余['.$yue.']'.'转入余额',
						'time' => $time,
						'oid' => $wxhb['id'].'-'.$wxhbson['id']
					));			
				}
				
			}
			if(($wxhb['isson']==0) && ($wxhb['num']==1)){
				//单日
				if($wxhb['endtime'] < $time){ //红包已过期
					//清除红包剩余余额
					$yue = $wxhb['yue'];
					M('Wxhb')->where('id='.$wxhb['id'])->save(array('yue'=>0));
					
					//剩余余额返还商户余额
					$shopid = $wxhb['shopid'];
					$uid = M('Document')->where(array('id'=>$shopid))->getField('uid');
					$shopname = M('Document')->where(array('id'=>$shopid))->getField('title');
					M('WxuserCode')->where('id='.$uid)->setInc('yue',$yue);
					
					//商户余额记录
					M('WxuserYuelog')->add(array(
						'uid' => $uid,
						'fee' => $yue,
						'desc' => '红包店['.$shopname.']剩余['.$yue.']'.'转入余额',
						'time' => $time,
						'oid' => $wxhb['id'].'-0'
					));					
				}
			}
		}
	}
	
	/**
	 * 发红包
	 */
    public function sendhb(){
    	$time = time();
		
    	$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$shopid = I('get.shopid');
		$this->assign('shopid',$shopid);
			
		$shop_title = M('Document')->where('id='.$shopid)->getField('title');
		$this->assign('shop_title',$shop_title);
			
		//查找店铺红包
		$hb = M('Wxhb')->where('shopid='.$shopid.' and endtime > '.$time.' and yue>0 and ispay=1')->find();
		if($hb){
			//已经存在红包
			$this->assign('hb',$hb);
			
			if($hb['isson'] && ($hb['num']>1)){
				$hbsons = M('WxhbSon')->where('hbid='.$hb['id'])->order('gettime asc')->select();
				$this->assign('hbsons',$hbsons);
			}
			
			$this->display('hbdetail');
		}else{		
			$sxf = M('Config')->where('id=39')->getField('value');
			$this->assign('sxf',$sxf);
			
	    	$this->display();
		}
    }
	
    public function dosendhb(){
		$time = time();
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		
		$_type = I('post.type');
		$ptmoney = I('post.ptmoney');
		$psqmoney1 = I('post.psqmoney1');
		$psqmoney2 = I('post.psqmoney2');
		$_ptmoney = M('Config')->where('id=41')->getField('value');
		$_psqmoney1 = M('Config')->where('id=42')->getField('value');
		$everymoney = I('post.everymoney');
		if($_type == '普通'){
			$type = 0; 
			if($ptmoney < $_ptmoney){
				$this->error('发布失败,普通最低金额不满足条件');die;
			}
			//单个被抢红包 最低金额不可低于0.1元
			$onemoney = $everymoney / $ptmoney;
			if($onemoney < 0.1){
				$this->error('单个被抢红包,最低金额不可低于0.1元');die;
			}
		}
		if($_type == '拼手气'){
			$type = 1; 
			if($psqmoney1 < $_psqmoney1){
				$this->error('发布失败,区间最低金额不满足条件');die;
			}
			if($psqmoney1 > $psqmoney2){
				$this->error('区间最低金额不能大于最高金额');die;
			}
			//单个被抢红包 最低金额不可低于0.1元
			$onemoney = $everymoney / $psqmoney2;
			if($onemoney < 0.1){
				$this->error('单个被抢红包,最低金额不可低于0.1元');die;
			}
		}
				
		$_gettime = I('post.gettime');
		$gettime = strtotime($_gettime);
		
		$deadline = M('Config')->where('id=40')->getField('value');
		$endtime = $gettime + $deadline * 24*3600;
		
		$ordersn = 'WXHB'.substr( md5('NNN'.time()) , 4,12);
		
		$array = array(
			'shopid' => I('post.shopid'),
			'money' => I('post.money'),
			'sxf' => I('post.sxf'),
			'everymoney' => $everymoney,
			'num' => I('post.num'),
			'yue' => I('post.money') - I('post.sxf'), 
			'type' => $type,
			'ptmoney' => $ptmoney,
			'psqmoney1' => $psqmoney1,
			'psqmoney2' => $psqmoney2,
			'iskl' => I('post.iskl'),
			'kl' => I('post.kl'),
			'createtime' => $time,
			'endtime' => $endtime,
			'area' => I('post.area'),
			'gettime' => $gettime,
			'sn' => $ordersn
   		);
		$id = M('Wxhb')->add($array);
		if($id){
			$this->success('发布成功',U('Shop/hbpay',array('id'=>$id)));
		}else{
			$this->error('发布失败');
		}
	}

	public function hbpay(){
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$id = I('get.id');
		$wxhb =  M('Wxhb')->find($id);
		$ordersn =  $wxhb['sn'];
		$this->assign('ordersn',$ordersn);
		$pay_price = $wxhb['money'];
		$this->assign('pay_price',$pay_price);
		
		$this->display();
	}
	
	public function wxhbpay(){
		
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$ordersn = I('get.ordersn');
		$price = I('get.price');
		$type = I('get.type');
				
		//验证 （订单号 + 金额）		
		$info = M('Wxhb')->where('sn="'.$ordersn.'"')->find();
		if(!$info){
			die('请重新申请！');
		}
		
		$body = '红包支付';
		$out_trade_no = $ordersn;
		$total_fee = $price * 100;
		$notify_url = 'http://'.$_SERVER['HTTP_HOST'].'/wechat/wxhbpayres.php';
				
		if($type == '1'){
			//微信支付

			$pay = & load_wechat('Pay');
			$prepayid = $pay->getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI");
			if($prepayid===FALSE){
				header("Content-type:text/html;charset=utf-8");
			    echo '1--'.$pay->errMsg;die;
			}
			$pay_options = $pay->createMchPay($prepayid);			
			if($pay_options===FALSE){
				header("Content-type:text/html;charset=utf-8");
			    echo '2--'.$pay->errMsg;die;
			}
			$pay_options = json_encode($pay_options);
			$this->assign('pay_options',$pay_options);	
			
			$script = &  load_wechat('Script');
			$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
			$options = $script->getJsSign($thisurl);
			$options = json_encode($options);	
			if($options===FALSE){
				header("Content-type:text/html;charset=utf-8");
			    echo '3--'.$script->errMsg;die;
			}
			$this->assign('options',$options);	
						
			$this->display('wxhbpay');

		}elseif($type == '2'){
			//余额支付
			$useryue = $user['yue'];
			$waitpay = $price;
			if($useryue < $waitpay){
				$this->error('抱歉，您的余额不足！');
			}
			
			//扣除余额
			M('WxuserCode')->where('id='.$user['id'])->setDec('yue',$waitpay);
			//存余额记录
			M('WxuserYuelog')->add(array(
				'uid' => $user['id'],
				'fee' => '-'.$waitpay,
				'desc' => '发放店铺红包，支付余额['.$waitpay.']',
				'time' => time(),
				'oid' => '0-0'
			));
			//完成支付后逻辑		
			$data = array(
				'openid' => $openid,
				'ordersn' => $ordersn,
				'total_fee' => $total_fee
			);
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/nn.php?s=/Pub/hbpay_success.html';
			$this->_request($url,false,'post',$data);
			$this->success('恭喜您，发放店铺红包成功！',U('dians'));
		}

	}
	
	//点赞
	public function ajaxZan(){
		//test
		$id = I('get.id');
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$uid = $user['id'];
		$flag = M("Zan")->where(array('uid'=>$uid,'sid'=>$id))->count();
		if($flag==0){
			$num = M("Zan")->add(array('uid'=>$uid,'sid'=>$id));
			echo 1;
			exit;
		}else{
			echo 0;
			exit;
		}
	}
	//浏览量
	public function ajaxViews(){
		$id = I('get.id');
		M("Document")->where(array('id'=>$id))->setInc("views");
		$views = M("Document")->where(array('id'=>$id))->getField("views");
		echo $views;
	}
	//收藏
	public function ajaxCollection(){
		$id = I('get.id');
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$uid = $user['id'];
		$flag = M("Collection")->where(array('uid'=>$uid,'sid'=>$id))->count();
		if($flag==0){
			$num = M("Collection")->add(array('uid'=>$uid,'sid'=>$id));
			echo 1;
			exit;
		}else{
			echo 0;
			exit;
		}
	}
	
	/**
	 * 抢红包
	 */
    public function qianghb(){
    	$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$latlon = M('WxuserLatlon')->where(array('uid'=>$user['id']))->order('id desc')->find();
		
		$kl = I('get.kl');		
		
		$shopid = I('get.shopid');
		$dianshop = M('DocumentShop')->find($shopid);
		
		$jvli = $this->getDistance($latlon['lon'],$latlon['lat'],$dianshop['longitude'],$dianshop['latitude'],2);
		
		$time = time();
		$wxhb = M('Wxhb')->where('shopid='.$shopid.' and ispay=1 and yue>0')->find();
		if($wxhb){
			if($wxhb['isson'] && ($wxhb['num']>1)){
				//多日
				$wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 and gettime<='.$time.' and endtime>='.$time)->find();
				if($wxhbson){
					if($wxhbson['type'] == 0){
						//普通红包
						$this->gethbmoney('WxhbSon',$wxhbson['id'],$user['id'],$wxhbson['ptmoney'],$kl,$jvli);
					}
					if($wxhbson['type'] == 1){
						//拼手气红包
						$hbm = mt_rand($wxhbson['psqmoney1']*100,$wxhbson['psqmoney2']*100) /100;
						$this->gethbmoney('WxhbSon',$wxhbson['id'],$user['id'],$hbm,$kl,$jvli);
					}
				}else{
					//不可抢
					$_wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 ')->find();
					if($time < $_wxhbson['gettime']){
						echo json_encode(array('i'=>0,'msg'=>'未到红包发放时间'));
					}
					if( ($time >= $_wxhbson['gettime']) && ($time <= $_wxhbson['endtime']) ){
						if($_wxhbson['type'] == 0){
							//普通红包
							$this->gethbmoney('WxhbSon',$_wxhbson['id'],$user['id'],$_wxhbson['ptmoney'],$kl,$jvli);
						}
						if($_wxhbson['type'] == 1){
							//拼手气红包
							$hbm = mt_rand($wxhbson['psqmoney1']*100,$wxhbson['psqmoney2']*100) /100;
							$this->gethbmoney('WxhbSon',$_wxhbson['id'],$user['id'],$hbm,$kl,$jvli);
						}
					}
					if($time > $_wxhbson['endtime']){
						echo json_encode(array('i'=>0,'msg'=>'红包发放时间已结束'));
					}
				}
			}
			if(($wxhb['isson']==0) && ($wxhb['num']==1)){
				//单日
				if($time < $wxhb['gettime']){
					$this->assign('gettime',date('Y-m-d H:i:s',$wxhb['gettime']));
					echo json_encode(array('i'=>0,'msg'=>'未到红包发放时间'));
				}
				if( ($time >= $wxhb['gettime']) && ($time <= $wxhb['endtime']) ){
					if($wxhb['type'] == 0){
						//普通红包
						$this->gethbmoney('Wxhb',$wxhb['id'],$user['id'],$wxhb['ptmoney'],$kl,$jvli);
					}
					if($wxhb['type'] == 1){
						//拼手气红包
						$hbm = mt_rand($wxhb['psqmoney1']*100,$wxhb['psqmoney2']*100) /100;
						$this->gethbmoney('Wxhb',$wxhb['id'],$user['id'],$hbm,$kl,$jvli);
					}
				}
				if($time > $wxhb['endtime']){
					echo json_encode(array('i'=>0,'msg'=>'红包发放时间已结束'));
				}
			}
		}else{
			echo json_encode(array('i'=>0,'msg'=>'这个店铺没有红包啊~~'));
		}
		
    }

	private function gethbmoney($f,$hbid,$uid,$money,$kl,$jvli){
		$time = time();
		
		//扣除单日红包余额
		if($f == 'Wxhb'){
			$model = M('Wxhb');
			
			//判断距离
			$_jvli_type = $model->where('id='.$hbid)->getField('area');
			switch ($_jvli_type) {
				case '1':
					//3km 
					if( ($jvli>=0) && ($jvli<=3) ){
						
					}else{
						echo json_encode(array('i'=>0,'msg'=>'抱歉，您超过3km区域不可抢红包，赶快靠近抢吧！'));die;
					}
					break;
				case '2':
					//5km
					if( ($jvli>=0) && ($jvli<=5) ){
						
					}else{
						echo json_encode(array('i'=>0,'msg'=>'抱歉，您超过5km区域不可抢红包，赶快靠近抢吧！'));die;
					}
					break;
				case '3':
					//20km
					if( ($jvli>=0) && ($jvli<=20) ){
						
					}else{
						echo json_encode(array('i'=>0,'msg'=>'抱歉，您超过20km区域不可抢红包，赶快靠近抢吧！'));die;
					}
					break;
				case '4':
					//不限
					break;
				default:
					echo json_encode(array('i'=>0,'msg'=>'区域不正确！'));die;
					break;
			}
			
			//判断口令是否正确
			$iskl = $model->where('id='.$hbid)->getField('iskl');
			if($iskl){
				$realkl = $model->where('id='.$hbid)->getField('kl');
				if($realkl != $kl){
					echo json_encode(array('i'=>0,'msg'=>'抱歉，您输入的口令不正确！'));die;
				}
			}
			
			//判断是否抢过
			$log = M('WxuserYuelog')->where('uid='.$uid.' and oid="'.$hbid.'-0"')->find();
			if($log){
				echo json_encode(array('i'=>0,'msg'=>'抱歉，您已抢过该红包了！'));die;
			}
			
			$model->startTrans();
			$res = $model->where('id='.$hbid)->setDec('yue',$money);
			if($res){
				$model->commit();
				//增加余额			
				M('WxuserCode')->where('id='.$uid)->setInc('yue',$money);
				
				//存余额记录
				$shopid = $model->where('id='.$hbid)->getField('shopid');
				$shopname = M('Document')->where('id='.$shopid)->getField('title');
				M('WxuserYuelog')->add(array(
					'uid' => $uid,
					'fee' => $money,
					'desc' => '在红包店['.$shopname.']抢到['.$money.']'.'转入余额',
					'time' => $time,
					'oid' => $hbid.'-0'
				));
				echo json_encode(array('i'=>1,'msg'=>'真棒！抢到 '.$money.' 已存入余额。'));die;
			}else{
				$model->rollback();
				
				//红包已领完，若有剩余返还余额，发模板消息提醒
				$yue = 	$model->where('id='.$hbid)->getField('yue');
				$shopid = $model->where('id='.$hbid)->getField('shopid');
				$shopuid = M('Document')->where('id='.$shopid)->getField('uid');
				if($shopuid){
					$shopopenid = M('WxuserCode')->where('id='.$shopuid)->getField('openid');
				
					if($yue){
						//增加余额			
						M('WxuserCode')->where('id='.$shopuid)->setInc('yue',$yue);
						
						//减少余额
						$model->where('id='.$hbid)->setDec('yue',$yue);
						
						//存余额记录
						M('WxuserYuelog')->add(array(
							'uid' => $shopuid,
							'fee' => $yue,
							'desc' => '您的红包['.$hbid.']剩余['.$yue.']'.'转入余额',
							'time' => $time,
							'oid' => $hbid.'-0'
						));
					}
					
					$this->sendmessage($shopopenid,'您的红包已被领完，您又可以继续发红包了！~');
				}
					
				
				echo json_encode(array('i'=>0,'msg'=>'呀！红包未抢到~'));die;
			}
		}
		//扣除多日红包余额
		if($f == 'WxhbSon'){
			$model1 = M('Wxhb');
			$model2 = M('WxhbSon');
			
			$wxhbsonid = $hbid;
			$wxhbid = $model2->where('id='.$wxhbsonid)->getField('hbid');
			
			//判断距离
			$_jvli_type = $model->where('id='.$wxhbsonid)->getField('area');
			switch ($_jvli_type) {
				case '1':
					//3km 
					if( ($jvli>0) && ($jvli<=3) ){
						
					}else{
						echo json_encode(array('i'=>0,'msg'=>'抱歉，您超过3km区域不可抢红包，赶快靠近抢吧！'));die;
					}
					break;
				case '2':
					//5km
					if( ($jvli>0) && ($jvli<=5) ){
						
					}else{
						echo json_encode(array('i'=>0,'msg'=>'抱歉，您超过5km区域不可抢红包，赶快靠近抢吧！'));die;
					}
					break;
				case '3':
					//20km
					if( ($jvli>0) && ($jvli<=20) ){
						
					}else{
						echo json_encode(array('i'=>0,'msg'=>'抱歉，您超过20km区域不可抢红包，赶快靠近抢吧！'));die;
					}
					break;
				case '4':
					//不限
					break;
				default:
					echo json_encode(array('i'=>0,'msg'=>'区域不正确！'));die;
					break;
			}
			
			//判断口令是否正确
			$iskl = $model2->where('id='.$wxhbsonid)->getField('iskl');
			if($iskl){
				$realkl = $model2->where('id='.$wxhbsonid)->getField('kl');
				if($realkl != $kl){
					echo json_encode(array('i'=>0,'msg'=>'抱歉，您输入的口令不正确！'));die;
				}
			}
			
			//判断是否抢过
			$log = M('WxuserYuelog')->where('uid='.$uid.' and oid="'.$wxhbid.'-'.$wxhbsonid.'"')->find();
			if($log){
				echo json_encode(array('i'=>0,'msg'=>'抱歉，您已抢过该红包了！'));die;
			}
			
			$model1->startTrans();
			$res1 = $model1->where('id='.$wxhbid)->setDec('yue',$money);
			$res2 = $model2->where('id='.$wxhbsonid)->setDec('yue',$money);
			if($res1 && $res2){
				$model1->commit();
				//增加余额			
				M('WxuserCode')->where('id='.$uid)->setInc('yue',$money);
				
				//存余额记录
				$shopid = $model1->where('id='.$wxhbid)->getField('shopid');
				$shopname = M('Document')->where('id='.$shopid)->getField('title');				
				M('WxuserYuelog')->add(array(
					'uid' => $uid,
					'fee' => $money,
					'desc' => '在红包店['.$shopname.']抢到['.$money.']'.'转入余额',
					'time' => $time,
					'oid' => $wxhbid.'-'.$wxhbsonid
				));
				echo json_encode(array('i'=>1,'msg'=>'真棒！抢到 '.$money.' 已存入余额。'));die;
			}else{
				$model1->rollback();
				
				//红包已领完，若有剩余返还余额，发模板消息提醒
				$yue = 	$model2->where('id='.$wxhbsonid)->getField('yue');
				$shopid = $model1->where('id='.$wxhbid)->getField('shopid');
				$shopuid = M('Document')->where('id='.$shopid)->getField('uid');
				if($shopuid){
					$shopopenid = M('WxuserCode')->where('id='.$shopuid)->getField('openid');
				
					if($yue){
						//增加余额			
						M('WxuserCode')->where('id='.$shopuid)->setInc('yue',$yue);
						
						//减少余额
						$model2->where('id='.$wxhbsonid)->setDec('yue',$yue);
						
						//存余额记录
						M('WxuserYuelog')->add(array(
							'uid' => $shopuid,
							'fee' => $yue,
							'desc' => '您的红包['.$hbid.']剩余['.$yue.']'.'转入余额',
							'time' => $time,
							'oid' => $hbid.'-0'
						));
					}
					
					$this->sendmessage($shopopenid,'您的今日红包已被领完，别忘了明天还能抢红包啊！~');
				}
				
				echo json_encode(array('i'=>0,'msg'=>'呀！红包未抢到~'));die;
			}
		}
		
	}
	
	/**
	 * 计算两点地理坐标之间的距离
	 * @param  Decimal $longitude1 起点经度
	 * @param  Decimal $latitude1  起点纬度
	 * @param  Decimal $longitude2 终点经度 
	 * @param  Decimal $latitude2  终点纬度
	 * @param  Int     $unit       单位 1:米 2:公里
	 * @param  Int     $decimal    精度 保留小数位数
	 * @return Decimal
	 */
	private function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit=2, $decimal=2){
	
	    $EARTH_RADIUS = 6370.996; // 地球半径系数
	    $PI = 3.1415926;
	
	    $radLat1 = $latitude1 * $PI / 180.0;
	    $radLat2 = $latitude2 * $PI / 180.0;
	
	    $radLng1 = $longitude1 * $PI / 180.0;
	    $radLng2 = $longitude2 * $PI /180.0;
	
	    $a = $radLat1 - $radLat2;
	    $b = $radLng1 - $radLng2;
	
	    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
	    $distance = $distance * $EARTH_RADIUS * 1000;
	
	    if($unit==2){
	        $distance = $distance / 1000;
	    }
	
	    return round($distance, $decimal);
	
	}

	/**
	 * 抢红包记录
	 */
    public function logs(){
    	$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
    	$hbid = I('get.hbid');
		$hbyue = M('Wxhb')->where('id='.$hbid)->getField('yue');
		$this->assign('hbyue',$hbyue);	
		
		$logs = M('WxuserYuelog')->alias('a')
				->field('a.*,b.nickname')
				->join('left join onethink_wxuser_code b on a.uid=b.id')
				->where('a.oid like "'.$hbid.'-%"')
				->order('time desc')
				->select();
		$this->assign('logs',$logs);
		$this->display();	
    }
	
	/**
	 * 8秒后 判断一下  是否可抢
	 */
	private function check_user_can_qhb($dianid){
		$back = array('i'=>1,'msg'=>'');
		
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$latlon = M('WxuserLatlon')->where(array('uid'=>$user['id']))->order('id desc')->find();
		
		$shopid = $dianid;
		$dianshop = M('DocumentShop')->find($shopid);
		
		$jvli = $this->getDistance($latlon['lon'],$latlon['lat'],$dianshop['longitude'],$dianshop['latitude'],2);
		
		$time = time();
		$wxhb = M('Wxhb')->where('shopid='.$shopid.' and ispay=1 and yue>0')->find();
		if($wxhb){
			if($wxhb['isson'] && ($wxhb['num']>1)){
				//多日
				$wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 and gettime<='.$time.' and endtime>='.$time)->find();
				if($wxhbson){
					if($wxhbson['type'] == 0){
						//普通红包
						$back = $this->gethbmoney2('WxhbSon',$wxhbson['id'],$user['id'],$wxhbson['ptmoney'],$jvli);
					}
					if($wxhbson['type'] == 1){
						//拼手气红包
						$hbm = mt_rand($wxhbson['psqmoney1']*100,$wxhbson['psqmoney2']*100)/100;
						$back = $this->gethbmoney2('WxhbSon',$wxhbson['id'],$user['id'],$hbm,$jvli);
					}
				}else{
					//不可抢
					$_wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 ')->find();
					if($time < $_wxhbson['gettime']){
						$back['i'] = 0;
						$back['msg'] = '未到红包发放时间';
						if($back['i'] == 0){ return $back; }
					}
					if( ($time >= $_wxhbson['gettime']) && ($time <= $_wxhbson['endtime']) ){
						if($_wxhbson['type'] == 0){
							//普通红包
							$back = $this->gethbmoney2('WxhbSon',$_wxhbson['id'],$user['id'],$_wxhbson['ptmoney'],$jvli);
						}
						if($_wxhbson['type'] == 1){
							//拼手气红包
							$hbm = mt_rand($_wxhbson['psqmoney1']*100,$_wxhbson['psqmoney2']*100)/100;
							$back = $this->gethbmoney2('WxhbSon',$_wxhbson['id'],$user['id'],$hbm,$jvli);
						}
					}
					if($time > $_wxhbson['endtime']){
						$back['i'] = 0;
						$back['msg'] = '红包发放时间已结束';
						if($back['i'] == 0){ return $back; }
					}
				}
			}
			if(($wxhb['isson']==0) && ($wxhb['num']==1)){
				//单日
				if($time < $wxhb['gettime']){
					$back['i'] = 0;
					$back['msg'] = '未到红包发放时间';
					if($back['i'] == 0){ return $back; }
				}
				if( ($time >= $wxhb['gettime']) && ($time <= $wxhb['endtime']) ){
					if($wxhb['type'] == 0){
						//普通红包
						$back = $this->gethbmoney2('Wxhb',$wxhb['id'],$user['id'],$wxhb['ptmoney'],$kl,$jvli);
					}
					if($wxhb['type'] == 1){
						//拼手气红包
						$hbm = mt_rand($wxhb['psqmoney1']*100,$wxhb['psqmoney2']*100)/100;
						$back = $this->gethbmoney2('Wxhb',$wxhb['id'],$user['id'],$hbm,$jvli);
					}
				}
				if($time > $wxhb['endtime']){
					$back['i'] = 0;
					$back['msg'] = '红包发放时间已结束';
					if($back['i'] == 0){ return $back; }
				}
			}
		}else{			
			$back['i'] = 0;
			$back['msg'] = '这个店铺没有红包啊~~';
			if($back['i'] == 0){ return $back; }
		}
		
		return $back;
	}

	private function gethbmoney2($f,$hbid,$uid,$money,$jvli){
		$back = array('i'=>1,'msg'=>'');
		
		$time = time();
		
		//单日红包
		if($f == 'Wxhb'){
			$model = M('Wxhb');
			
			//判断距离
			$_jvli_type = $model->where('id='.$hbid)->getField('area');
			switch ($_jvli_type) {
				case '1':
					//3km 
					if( ($jvli>=0) && ($jvli<=3) ){
						
					}else{
						$back['i'] = 0;
						$back['msg'] = '抱歉，您超过3km区域不可抢红包，赶快靠近抢吧！';
					}
					break;
				case '2':
					//5km
					if( ($jvli>=0) && ($jvli<=5) ){
						
					}else{
						$back['i'] = 0;
						$back['msg'] = '抱歉，您超过5km区域不可抢红包，赶快靠近抢吧！';
					}
					break;
				case '3':
					//20km
					if( ($jvli>=0) && ($jvli<=20) ){
						
					}else{
						$back['i'] = 0;
						$back['msg'] = '抱歉，您超过20km区域不可抢红包，赶快靠近抢吧！';
					}
					break;
				case '4':
					//不限
					break;
				default:
					$back['i'] = 0;
					$back['msg'] = '区域不正确！';
					break;
			}
			if($back['i'] == 0){ return $back; }
			
			//判断是否抢过
			$log = M('WxuserYuelog')->where('uid='.$uid.' and oid="'.$hbid.'-0"')->find();
			if($log){				
				$back['i'] = 0;
				$back['msg'] = '抱歉，您已抢过该红包了！';
			}
			if($back['i'] == 0){ return $back; }
						
		}
		//多日红包
		if($f == 'WxhbSon'){
			$model1 = M('Wxhb');
			$model2 = M('WxhbSon');
			
			$wxhbsonid = $hbid;
			$wxhbid = $model2->where('id='.$wxhbsonid)->getField('hbid');
			
			//判断距离
			$_jvli_type = $model2->where('id='.$wxhbsonid)->getField('area');
			switch ($_jvli_type) {
				case '1':
					//3km 
					if( ($jvli>0) && ($jvli<=3) ){
						
					}else{
						$back['i'] = 0;
						$back['msg'] = '抱歉，您超过3km区域不可抢红包，赶快靠近抢吧！';
					}
					break;
				case '2':
					//5km
					if( ($jvli>0) && ($jvli<=5) ){
						
					}else{
						$back['i'] = 0;
						$back['msg'] = '抱歉，您超过5km区域不可抢红包，赶快靠近抢吧！';
					}
					break;
				case '3':
					//20km
					if( ($jvli>0) && ($jvli<=20) ){
						
					}else{
						$back['i'] = 0;
						$back['msg'] = '抱歉，您超过20km区域不可抢红包，赶快靠近抢吧！';
					}
					break;
				case '4':
					//不限
					break;
				default:
					$back['i'] = 0;
					$back['msg'] = '区域不正确！';
					break;
			}			
			if($back['i'] == 0){ return $back; }
									
			//判断是否抢过
			$log = M('WxuserYuelog')->where('uid='.$uid.' and oid="'.$wxhbid.'-'.$wxhbsonid.'"')->find();
			if($log){
				$back['i'] = 0;
				$back['msg'] = '抱歉，您已抢过该红包了！';
			}
			if($back['i'] == 0){ return $back; }
						
		}

		return $back;
	}

	//删除店铺
	public function delshop(){
		$shopid = I('id');
		
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		
		$wxhb = M('Wxhb')->where('shopid='.$shopid.' and yue>0 and ispay=1')->count();
		$wxhbson = M('WxhbSon')->where('shopid='.$shopid.' and yue>0')->count();
		
		if( ($wxhb > 0) || ($wxhbson > 0) ){
			$this->error('该店铺还有红包未领完，不可删除！');die;
		}else{
			M('Document')->delete($shopid);
			M('DocumentShop')->delete($shopid);
			$this->success('删除店铺成功！',U('dians'));die;
		}
		
		
	}
	
}