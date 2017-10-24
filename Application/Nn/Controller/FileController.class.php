<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Nn\Controller;

/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class FileController extends \Think\Controller {

    /* 文件上传 */
    public function upload(){
		$upload = new \Think\Upload();// 实例化上传类
	    $upload->maxSize   =     31457280 ;// 设置附件上传大小 30M
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg','JPG','GIF','PNG','JPEG');// 设置附件上传类型
	    $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
	    $upload->savePath  =     'Picture/'; // 设置附件上传（子）目录
	    // 上传文件 
	    $info   =   $upload->upload();
	    if(!$info) {// 上传错误提示错误信息
	    	$return = array(
				'code' => 0,
				'msg' => $upload->getError()
			);
	    }else{// 上传成功
	    	
	    	$path = '/Uploads/'.$info['file']['savepath'].$info['file']['savename'];
	    	$array = array(
				'path' => $path,
				'url' => '',
				'md5' => '',
				'sha1' => '',
				'status' => 1,
				'create_time' => time()
			);
			$newid = M('Picture')->add($array);
	    
	        $return = array(
				'code' => 1,
				'msg' => $path,
				'newid' => $newid
			);
	    }
		
	    echo json_encode($return);
	}
    
}
