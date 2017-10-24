<?php

namespace Admin\Controller;

class MemController extends AdminController {

    
    public function index(){
        
		$mem = M('WxuserCode');
		$count      = $mem->count();
		$Page       = new \Think\Page($count,10);
		$show       = $Page->show();
		$list = $mem->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		foreach ($list as $key => $value) {
						
			if($value['isbind'] == 0){
				$list[$key]['mobile'] = '未绑定';
			}
			$list[$key]['time'] = date('Y-m-d H:i',$value['time']);
			
			if($value['subscribe'] == 0){
				$list[$key]['subscribe'] = '未关注';
			}elseif($value['subscribe'] == 1){
				$list[$key]['subscribe'] = '已关注';
			}elseif($value['subscribe'] == 2){
				$list[$key]['subscribe'] = '已取消';
			}
			
		}
		
		$this->assign('_page',$show);		
        $this->assign('_list', $list);
        $this->meta_title = '会员信息';
        $this->display();
    }
	
	public function info(){
		$id = I('get.id');
		$mem = M('WxuserCode');
		$_info = $mem->find($id);
		
		if($_info['isbind'] == 0){
			$_info['isbind'] = '未绑定';
		}else{
			$_info['isbind'] = $_info['mobile'];
		}
		
		if($_info['subscribe'] == 0){
			$_info['subscribe'] = '未关注';
		}elseif($_info['subscribe'] == 1){
			$_info['subscribe'] = '已关注';
		}elseif($_info['subscribe'] == 2){
			$_info['subscribe'] = '已取消';
		}
			
		if($_info['sex'] == 1){
			$_info['sex'] = '男';
		}elseif($_info['sex'] == 2){
			$_info['sex'] = '女';
		}else{
			$_info['sex'] = '未知';
		}
				
        $this->assign('_info', $_info);
        $this->meta_title = '会员详细信息';
        $this->display();
	}

}