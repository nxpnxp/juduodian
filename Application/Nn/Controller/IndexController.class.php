<?php

namespace Nn\Controller;

class IndexController extends HomeController {

	
	//首页   nn.php?s=/index/index.html
    public function index(){
	
		$openid = $this->openid;
		
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$cates = M('Category')->alias('c')
			->field('c.id,c.title,p.path')
			->join('left join onethink_picture p on c.icon=p.id')
			->where('c.pid=0')
			->limit(9)
			->select();
		$this->assign('cates',$cates);
		
		$morens = M('Document')->alias('d')
			->field('d.id,d.views,d.title,d.description,ds.content,p.path,ds.longitude,ds.latitude,ds.showaddress')
			->join('left join onethink_picture p on d.cover_id=p.id')
			->join('left join onethink_document_shop ds on ds.id=d.id')
			->where('d.status=1')
			->limit(10)
			->select();
		$_time = time();
		foreach($morens as $k=>$v){
			$morens[$k]['collection'] = M("Collection")->where(array('sid'=>$v['id']))->count();
			$morens[$k]['zan'] = M("Zan")->where(array('sid'=>$v['id']))->count();
			$flag = 0;
			$flag = M("Wxhb")->where("shopid={$v['id']} and $_time>=gettime and $_time <= endtime  and ispay=1")->count();
			if($flag <=0){
				$flag = M("WxhbSon")->where("shopid={$v['id']} and $_time>=gettime and $_time <= endtime")->count();
			}
			$morens[$k]['hb'] = $flag;
		}
		$this->assign('morens',$morens);
		
		$tjs = M('Document')->alias('d')
			->field('d.id,d.title,d.views,d.description,ds.content,p.path,ds.longitude,ds.latitude,ds.showaddress')
			->join('left join onethink_picture p on d.cover_id=p.id')
			->join('left join onethink_document_shop ds on ds.id=d.id')
			->where('d.status=1 and ds.istj=1')
			->limit(10)
			->select();
		$daybegin=strtotime(date("Ymd")); 
		$dayend=$daybegin+86400;
		foreach($tjs as $k=>$v){
			$tjs[$k]['collection'] = M("Collection")->where(array('sid'=>$v['id']))->count();
			$tjs[$k]['zan'] = M("Zan")->where(array('sid'=>$v['id']))->count();
			$flag = 0;
			$flag = M("Wxhb")->where("shopid={$v['id']} and $_time>=gettime and $_time<= endtime and ispay=1")->count();
			if($flag <=0){
				$flag = M("WxhbSon")->where("shopid={$v['id']} and $_time>=gettime and $_time<= endtime")->count();
			}
			$tjs[$k]['hb'] = $flag;
		}
		$this->assign('tjs',$tjs);
		
		//附近的店铺
		$visit = M("WxuserLatlon")->where(array('uid'=>$user['id']))->order("time desc")->find();
		$dianpu = M('Document')->alias('d')
			->field('d.id,d.title,d.views,d.description,ds.content,p.path,ds.longitude,ds.latitude,ds.showaddress')
			->join('left join onethink_picture p on d.cover_id=p.id')
			->join('left join onethink_document_shop ds on ds.id=d.id')
			->where('d.status=1')
			->select();
		foreach($dianpu as $k=>$v){
			$dianpu[$k]['juli'] = $this->getDistance($v['longitude'], $v['latitude'],$visit['lon'], $visit['lat']);
		}

		foreach ($dianpu as $key => $row)
		{
			$volume[$key]  = $row['juli'];
			$edition[$key] = $row['id'];
		}
	
		array_multisort($volume, SORT_ASC, $edition, SORT_ASC, $dianpu);
	    
		//$dianpu1 = array_slice($dianpu,0,10);
		$dianpu1 = $dianpu;
		foreach($dianpu1 as $k=>$v){
			$dianpu1[$k]['collection'] = M("Collection")->where(array('sid'=>$v['id']))->count();
			$dianpu1[$k]['zan'] = M("Zan")->where(array('sid'=>$v['id']))->count();
			$flag = 0;
			$flag = M("Wxhb")->where("shopid={$v['id']} and $_time>=gettime and $_time <= endtime and ispay=1")->count();
			if($flag <=0){
				$flag = M("WxhbSon")->where("shopid={$v['id']} and $_time>=gettime and $_time <= endtime")->count();
			}
			$dianpu1[$k]['hb'] = $flag;
			
		}
		$this->assign('fujin',$dianpu1);


		$script = &  load_wechat('Script');
		$thisurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$options = $script->getJsSign($thisurl);
		$options = json_encode($options);
					
		if($options===FALSE){
		    echo $script->errMsg;die;
		}else{
			$this->assign('options',$options);
			
			$this->display();
		}
		
    	
	}
		
		
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
	
	//设置自定义菜单  nn.php?s=/index/setmenu.html
    public function setmenu(){
		
		$_url = C('__URL__');
		
		$menu = & load_wechat('Menu');
		
		$data = array(
			'button' => array(
				array(
					'type' => 'view',
					'name' => '主页',
					'url' => $_url.'nn.php?s=/index/index.html'
				),
				array(
					'name' => '店铺',
					'sub_button' => array(
						array(
							'type' => 'view',
							'name' => '申请开店',
							'url' => $_url.'nn.php?s=/Shop/index.html'
						),
						array(
							'type' => 'view',
							'name' => '我的店铺',
							'url' => $_url.'nn.php?s=/Shop/dians.html'
						),
					)
				),
				array(
					'type' => 'view',
					'name' => '会员中心',
					'url' => $_url.'nn.php?s=/Member/index.html'
				)
			)
		);
    	
		// 创建微信菜单
		$result = $menu->createMenu($data);
		
		// 处理创建结果
		if($result===FALSE){
		    // 接口失败的处理
		    echo $menu->errMsg;
		}else{
		    // 接口成功的处理
		    echo 'ook';
		}
		
	}
	
	//获取自定义菜单  nn.php?s=/index/getmenu.html
    public function getmenu(){
		
		$menu = & load_wechat('Menu');
		
		$result = $menu->getMenu();
		
		// 处理创建结果
		if($result===FALSE){
		    // 接口失败的处理
		    echo $menu->errMsg;
		}else{
		    // 接口成功的处理
		    header("Content-Type: text/html; charset=UTF-8");
		    var_dump($result);
		}
	}
	
	//用户取消关注事件  nn.php?s=/index/unsubscribe.html
    public function unsubscribe(){
		$openid = I('post.openid');
		$search = M('WxuserCode')->where(array('openid'=>$openid))->find();
		if($search){
			$array = array();
			$array['subscribe'] = 2;
			M('WxuserCode')->where(array('id'=>$search['id']))->save($array);
		}		
	}
	
	//用户关注事件  nn.php?s=/index/subscribe.html
    public function subscribe(){
		$openid = I('post.openid');
		$search = M('WxuserCode')->where(array('openid'=>$openid))->find();
		if($search){
			$array = array();
			$array['subscribe'] = 1;
			M('WxuserCode')->where(array('id'=>$search['id']))->save($array);
			
			//原无gzh_info 现获取用户信息
			if(empty($search['gzh_info'])){
				$this->get_user_info($openid);
			}
			
		}		
	}
	
	//用户关注事件  原无gzh_info 现获取用户信息
	private function get_user_info($openid){
		$user = & load_wechat('User');
		$result = $user->getUserInfo($openid);
		if ($result === FALSE) {
		    echo $user->errMsg;
		    echo $user->errCode;
		    exit;
		} else {
			//保存用户信息
		    $array = array();
			$array['gzh_info'] = serialize($result);
			$array['nickname'] = $result['nickname'];
			$array['sex'] = $result['sex'];
			$array['country'] = $result['country'];
			$array['province'] = $result['province'];
			$array['city'] = $result['city'];
			$array['headimgurl'] = $result['headimgurl'];		
			M('WxuserCode')->where(array('openid'=>$openid))->save($array);
		}
	}
	
	//绑定手机号
    public function bind(){
    	
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
    	$this->display();
	}
	
	public function sendsms(){
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		
		$mobile = I('post.mobile');
		if($user['id'] && $mobile){
			$code = mt_rand(10000,99999);
			$data = array(
				'codeid' => $user['id'],
				'mobile' => $mobile,
				'yzm' => $code,
				'createtime' => time()
			);
			$res = M('WxuserYzm')->add($data);
			echo $res;
			$_url = C('__URL__');
			$url = $_url.'aliyunsms/AliyunMNS/Sms.php?c='.$mobile.'&i='.$code;
			$this->_request($url, false, 'get', null);
		}else{
			echo 0;
		}
	}
	
	public function dobind(){
    			
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		if(!$user){ $this->error('绑定失败，Err: B001'); }
		
		$mobile = I('post.mobile');
		$yzm = I('post.yzm');
		
		$yzminfo = M('WxuserYzm')->where('codeid='.$user['id'].' and mobile="'.$mobile.'"')->order('createtime desc')->find();
		if(!$yzminfo){$this->error('绑定失败，Err: B002'); }
		
		if($yzm == $yzminfo['yzm']){
			$data = array(
				'isbind' => 1,
				'mobile' => $mobile
			);
			$res = M('WxuserCode')->where('id='.$user['id'])->save($data);
			if($res){
				$this->success('绑定成功', U('Index/index'));
			}else{
				$this->error('绑定失败，Err: B004');
			}
		}else{
			$this->error('绑定失败，Err: B003');
		}
		
	}
	
	//需要关注
	public function needgz(){
		$this->display();
	}
	
	
}