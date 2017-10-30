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
		//。。。
		
		$dian = M('Document')->alias('d')
				->field("d.*,ds.*,p.path")
				->join('left join onethink_document_shop ds on d.id=ds.id')
				->join('left join onethink_picture p on ds.imgs=p.id')
				->where('d.id='.$id)->find();
		$this->assign('dian',$dian);
		
		$wait = 8;
		$flag = 0;//0没红包 1未开始 2疯抢中 3已抢完 4已结束
		$time = time();
		$wxhb = M('Wxhb')->where('shopid='.$id.' and ispay=1 and yue>0')->find();
		if($wxhb){
			if($wxhb['isson'] && ($wxhb['num']>1)){
				//多日
			}
			if(($wxhb['isson']==0) && ($wxhb['num']==1)){
				//单日
			}
		}
		
    	$this->display();
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
		$hb = M('Wxhb')->where('shopid='.$shopid.' and endtime > '.$time.' and ispay=1')->find();
		if($hb){
			//已经存在红包
			$this->assign('hb',$hb);
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
}