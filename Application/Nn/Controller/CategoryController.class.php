<?php

namespace Nn\Controller;

class CategoryController extends HomeController {

	
	//分类首页   nn.php?s=/category/index.html
    public function index(){
    		
		$cates = M('Category')->alias('c')
			->field('c.id,c.title,p.path')
			->join('left join onethink_picture p on c.icon=p.id')
			->where('c.pid=0')
			->select();
		$this->assign('cates',$cates);
		
		$pid = I('get.cateid');
		$this->assign('pid',$pid);
		
		$cates_son = M('Category')->alias('c')
			->field('c.id,c.title,p.path')
			->join('left join onethink_picture p on c.icon=p.id')
			->where('c.pid='.$pid)
			->select();
		foreach ($cates_son as $key => $value) {
			if(empty($value['path'])){
				$cates_son[$key]['path'] = '/Public/_img.png';
			}
		}
		$this->assign('cates_son',$cates_son);
		
    	$this->display();
	}
	
	//ajax 根据cate1获取cate2
	public function getcate2(){
		$cate1 = I('post.cate1');
		$return = array('code' => 0,'msg' => 'cate1 error');
		if($cate1 > 0){
			
			$cate2 = M('Category')->field('id,title')->where(array('pid'=>$cate1))->order('sort asc')->select();
			$return['code'] = 1;
			$return['msg'] = $cate2;
			
		}
		$this->ajaxReturn($return);
	}
	
	public function showall(){
		$cateid = I('get.id');
		$catename = M('Category')->where(array('id'=>$cateid))->getField('title');
		$this->assign('catename',$catename);
		
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$this->assign('user',$user);
		
		$visit = M("WxuserLatlon")->where(array('uid'=>$user['id']))->order("time desc")->find();
		
		$morens = M('Document')->alias('d')
			->field('d.id,d.title,d.views,d.description,ds.content,p.path,ds.longitude,ds.latitude,ds.showaddress')
			->join('left join onethink_picture p on d.cover_id=p.id')
			->join('left join onethink_document_shop ds on ds.id=d.id')
			->where('d.status=1 and d.category_id='.$cateid)
			->select();

		foreach($morens as $k=>$v){
			$morens[$k]['juli'] = $this->getDistance($v['longitude'], $v['latitude'],$visit['lon'], $visit['lat']);
		}

		foreach ($morens as $key => $row)
		{
			$volume[$key]  = $row['juli'];
			$edition[$key] = $row['id'];
		}
	
		array_multisort($volume, SORT_ASC, $edition, SORT_ASC, $morens);
	    
		$morens1 = array_slice($morens,0,10);
		$daybegin=strtotime(date("Ymd")); 
		$dayend=$daybegin+86400;
		foreach($morens1 as $k=>$v){
			$morens1[$k]['collection'] = M("Collection")->where(array('sid'=>$v['id']))->count();
			$morens1[$k]['zan'] = M("Zan")->where(array('sid'=>$v['id']))->count();
			$morens1[$k]['juli'] = sprintf("%.2f", $v['juli']); 
			$flag = 0;
			$flag = M("Wxhb")->where("shopid={$v['id']} and $daybegin>gettime and $dayend < endtime and ispay=1")->count();
			if($flag <=0){
				$flag = M("Wxhb")->where("shopid={$v['id']} and $daybegin>gettime and $dayend < endtime")->count();
			}
			$morens[$k]['hb'] = $flag;
		}
		$this->assign('morens',$morens1);
		
		$this->display();
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
	
	
	
}