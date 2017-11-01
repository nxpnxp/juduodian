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
					$imgs_id = $dian_shop['imgs'];
					if($cover_id > 1){
						$cover = M('Picture')->find($cover_id);
						$cover_src = $cover['path'];
						$this->assign('cover_src',$cover_src);
					}
					if($imgs_id > 1){
						$imgs = M('Picture')->find($imgs_id);
						$imgs_src = $imgs['path'];
						$this->assign('imgs_src',$imgs_src);
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
			'cover_id' => I('post.newid1'), //封面
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
		
		$array2 = array(
			'id' => $id,
			'imgs' => I('post.newid2'), //店铺形象图
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
			'cover_id' => I('post.newid1'), //封面
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
			'imgs' => I('post.newid2'), //店铺形象图
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
				->field("d.*,ds.*,p.path")
				->join('left join onethink_document_shop ds on d.id=ds.id')
				->join('left join onethink_picture p on ds.imgs=p.id')
				->where('d.id='.$id)->find();
		$this->assign('dian',$dian);
		
		$wait = 8;
		$flag = 0;//0没红包 1未开始 2疯抢中 3已结束 4不可抢
		$time = time();
		$wxhb = M('Wxhb')->where('shopid='.$id.' and ispay=1 and yue>0')->find();
		if($wxhb){
			if($wxhb['isson'] && ($wxhb['num']>1)){
				//多日
				$wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 and gettime<='.$time.' and endtime>='.$time)->find();
				if($wxhbson){
					$this->assign('iskl',$wxhbson['iskl']);
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
					$flag = 2;//2疯抢中
				}
				if($time > $wxhb['endtime']){
					$flag = 3;//3已结束
				}
			}
		}
		$this->assign('wait',$wait);
		$this->assign('flag',$flag);
		
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
					M('WxuserCode')->where('id='.$uid)->setInc('yue',$yue);
					
					//商户余额记录
					M('WxuserYuelog')->add(array(
						'uid' => $uid,
						'fee' => $yue,
						'desc' => '红包子订单['.$wxhbson['id'].']剩余['.$yue.']'.'转入余额',
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
					M('WxuserCode')->where('id='.$uid)->setInc('yue',$yue);
					
					//商户余额记录
					M('WxuserYuelog')->add(array(
						'uid' => $uid,
						'fee' => $yue,
						'desc' => '红包['.$wxhb['id'].']剩余['.$yue.']'.'转入余额',
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
		$_ptmoney = M('Config')->where('id=41')->getField('value');
		$_psqmoney1 = M('Config')->where('id=42')->getField('value');
		if($_type == '普通'){
			$type = 0; 
			if($ptmoney < $_ptmoney){
				$this->error('发布失败,普通最低金额不满足条件');die;
			}
		}
		if($_type == '拼手气'){
			$type = 1; 
			if($psqmoney1 < $_psqmoney1){
				$this->error('发布失败,区间最低金额不满足条件');die;
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
			'everymoney' => I('post.everymoney'),
			'num' => I('post.num'),
			'yue' => I('post.money') - I('post.sxf'), 
			'type' => $type,
			'ptmoney' => $ptmoney,
			'psqmoney1' => $psqmoney1,
			'psqmoney2' => I('post.psqmoney2'),
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
						$hbm = mt_rand($wxhbson['psqmoney1'],$wxhbson['psqmoney2']);
						$this->gethbmoney('WxhbSon',$wxhbson['id'],$user['id'],$hbm,$kl,$jvli);
					}
				}else{
					//不可抢
					$_wxhbson = M('WxhbSon')->where('hbid='.$wxhb['id'].' and yue>0 ')->find();
					if($time < $_wxhbson['gettime']){
						$this->error('未到红包发放时间');
					}
					if( ($time >= $_wxhbson['gettime']) && ($time <= $_wxhbson['endtime']) ){
						if($_wxhbson['type'] == 0){
							//普通红包
							$this->gethbmoney('WxhbSon',$_wxhbson['id'],$user['id'],$_wxhbson['ptmoney'],$kl,$jvli);
						}
						if($_wxhbson['type'] == 1){
							//拼手气红包
							$hbm = mt_rand($_wxhbson['psqmoney1'],$_wxhbson['psqmoney2']);
							$this->gethbmoney('WxhbSon',$_wxhbson['id'],$user['id'],$hbm,$kl,$jvli);
						}
					}
					if($time > $_wxhbson['endtime']){
						$this->error('红包发放时间已结束');
					}
				}
			}
			if(($wxhb['isson']==0) && ($wxhb['num']==1)){
				//单日
				if($time < $wxhb['gettime']){
					$this->assign('gettime',date('Y-m-d H:i:s',$wxhb['gettime']));
					$this->error('未到红包发放时间');
				}
				if( ($time >= $wxhb['gettime']) && ($time <= $wxhb['endtime']) ){
					if($wxhb['type'] == 0){
						//普通红包
						$this->gethbmoney('Wxhb',$wxhb['id'],$user['id'],$wxhb['ptmoney'],$kl,$jvli);
					}
					if($wxhb['type'] == 1){
						//拼手气红包
						$hbm = mt_rand($wxhb['psqmoney1'],$wxhb['psqmoney2']);
						$this->gethbmoney('Wxhb',$wxhb['id'],$user['id'],$hbm,$kl,$jvli);
					}
				}
				if($time > $wxhb['endtime']){
					$this->error('红包发放时间已结束');
				}
			}
		}else{
			$this->error('这个店铺没有红包啊~~');
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
					if( ($jvli>0) && ($jvli<=3) ){
						
					}else{
						$this->error('抱歉，您超过3km区域不可抢红包，赶快靠近抢吧！');
					}
					break;
				case '2':
					//5km
					if( ($jvli>0) && ($jvli<=5) ){
						
					}else{
						$this->error('抱歉，您超过5km区域不可抢红包，赶快靠近抢吧！');
					}
					break;
				case '3':
					//20km
					if( ($jvli>0) && ($jvli<=20) ){
						
					}else{
						$this->error('抱歉，您超过20km区域不可抢红包，赶快靠近抢吧！');
					}
					break;
				case '4':
					//不限
					break;
				default:
					$this->error('区域不正确！');
					break;
			}
			
			//判断口令是否正确
			$iskl = $model->where('id='.$hbid)->getField('iskl');
			if($iskl){
				$realkl = $model->where('id='.$hbid)->getField('kl');
				if($realkl != $kl){
					$this->error('抱歉，您输入的口令不正确！');
				}
			}
			
			//判断是否抢过
			$log = M('WxuserYuelog')->where('uid='.$uid.' and oid="'.$hbid.'-0"')->find();
			if($log){
				$this->error('抱歉，您已抢过该红包了！');
			}
			
			$model->startTrans();
			$res = $model->where('id='.$hbid)->setDec('yue',$money);
			if($res){
				$model->commit();
				//增加余额			
				M('WxuserCode')->where('id='.$uid)->setInc('yue',$money);
				
				//存余额记录
				M('WxuserYuelog')->add(array(
					'uid' => $uid,
					'fee' => $money,
					'desc' => '红包['.$hbid.']抢到['.$money.']'.'转入余额',
					'time' => $time,
					'oid' => $hbid.'-0'
				));
				$this->success('真棒！抢到 '.$money.' 已存入余额。');
			}else{
				$model->rollback();
				$this->error('呀！红包未抢到~');
			}
		}
		//扣除多日红包余额
		if($f == 'WxhbSon'){
			$model1 = M('Wxhb');
			$model2 = M('WxhbSon');
			
			$wxhbsonid = $hbid;
			$wxhbid = $model2->where('id='.$wxhbsonid)->getField('hbid');
			
			//判断口令是否正确
			$iskl = $model2->where('id='.$wxhbsonid)->getField('iskl');
			if($iskl){
				$realkl = $model2->where('id='.$wxhbsonid)->getField('kl');
				if($realkl != $kl){
					$this->error('抱歉，您输入的口令不正确！');
				}
			}
			
			//判断是否抢过
			$log = M('WxuserYuelog')->where('uid='.$uid.' and oid="'.$wxhbid.'-'.$wxhbsonid.'"')->find();
			if($log){
				$this->error('抱歉，您已抢过该红包了！');
			}
			
			$model1->startTrans();
			$res1 = $model1->where('id='.$wxhbid)->setDec('yue',$money);
			$res2 = $model2->where('id='.$wxhbsonid)->setDec('yue',$money);
			if($res1 && $res2){
				$model1->commit();
				//增加余额			
				M('WxuserCode')->where('id='.$uid)->setInc('yue',$money);
				
				//存余额记录
				M('WxuserYuelog')->add(array(
					'uid' => $uid,
					'fee' => $money,
					'desc' => '红包子订单['.$wxhbsonid.']抢到['.$money.']'.'转入余额',
					'time' => $time,
					'oid' => $wxhbid.'-'.$wxhbsonid
				));
				$this->success('真棒！抢到 '.$money.' 已存入余额。');
			}else{
				$model1->rollback();
				$this->error('呀！红包未抢到~');
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
	
}