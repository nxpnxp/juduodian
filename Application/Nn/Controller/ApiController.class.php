<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Nn\Controller;
use Think\Controller;

class ApiController extends Controller {
	
	
	public function _empty(){
		$this->redirect('Index/index');
	}

    protected function _initialize(){
		    	
    }
	
	public function getsoncate(){
		$pcate = I('post.pcate');

		$cates_son = M('Category')->alias('c')
			->field('c.id,c.title,p.path')
			->join('left join onethink_picture p on c.icon=p.id')
			->where('c.pid='.$pcate)
			->select();
		foreach ($cates_son as $key => $value) {
			if(empty($value['path'])){
				$cates_son[$key]['path'] = '/Public/_img.png';
			}
		}
		echo json_encode($cates_son);
	}
			
	
}
