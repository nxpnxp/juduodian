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
	
	
	
}