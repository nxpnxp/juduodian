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
				'result'=>'failed',  
				'message' => $upload->getError()
			);
	    }else{// 上传成功
	    	
	    	$path = './Uploads/'.$info['file']['savepath'].$info['file']['savename'];
			$_path_arr = explode('.', $path);
			$_path = '.'.$_path_arr[1].'_thumb.'.$_path_arr[2];
			
			//修改成为缩略图
			$image = new \Think\Image(); 
			$image->open($path);
			$image->thumb(640, 480)->save($_path);
			
	    	
	    	$array = array(
				'path' => $_path,
				'url' => '',
				'md5' => '',
				'sha1' => '',
				'status' => 1,
				'create_time' => time()
			);
			$newid = M('Picture')->add($array);
	    
	        $return = array(
				'result' => 'ok',
				'id' => $newid,
				'url' => $path
				
			);
			
	    }
		
	    echo json_encode($return);
	}
    
	//curl

	private function _request($url, $https=true, $method='get', $data=null)

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

	//获取token

	public function getToken(){

		$_appid = 'wx08345ce6da3cdfb2';

		$_secret = '23caaf0913a9e6d6e1b40a8beda98764';

		$_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$_appid&secret=$_secret";

		$file = "./accesstoken.txt";

		$str = $this->_request($_url);

		file_put_contents($file,$str);

		$obj = json_decode($str);

		return $obj->access_token; 

	}

	//下载多媒体文件

	public function downMedia($mediaId){

		$_token = $this->getToken();

		$_url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$_token&media_id=$mediaId";

        $fileInfo = $this->downloadWeixinFile($_url);

		$_dir = date("Y-m",time());

		if(!file_exists('./Uploads/Picture/'.$_dir)){

			mkdir('./Uploads/Picture/'.$_dir);

		}

 		$filename = './Uploads/Picture/'.$_dir.'/'.rand(1,100000).time().'.jpg';

		$this->saveWeixinFile($filename,$fileInfo['body']);

		return $filename;

	}

	public function downloadWeixinFile($url){

		$ch = curl_init($url);

		curl_setopt($ch,CURLOPT_HEADER,0);

		curl_setopt($ch,CURLOPT_NOBODY,0);

		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);

		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		$package = curl_exec($ch);

		$httpinfo = curl_getinfo($ch);

		curl_close($ch);

		$imageAll = array_merge(array('header'=>$httpinfo),array('body'=>$package));

		return $imageAll;

	}

	public function saveWeixinFile($filename,$filecontent){

		$local_file = fopen($filename,'w');

		if(false !==$local_file){

			if(false != fwrite($local_file,$filecontent)){

				fclose($local_file);

			}

		}

	}
	
	public function uploads(){
		$srcs = I('post.srcs');
		$srcs = json_decode($srcs);
		
		$return = array();
		
		foreach ($srcs as $key => $value) {
			$file = $this->downMedia($value);
			
			$_path_arr = explode('.', $file);
			$_path = '.'.$_path_arr[1].'_thumb.'.$_path_arr[2];
			
			//修改成为缩略图
			$image = new \Think\Image(); 
			$image->open($file);
			$image->thumb(640, 480)->save($_path);
			
	    	
	    	$array = array(
				'path' => $_path,
				'url' => '',
				'md5' => '',
				'sha1' => '',
				'status' => 1,
				'create_time' => time()
			);
			$newid = M('Picture')->add($array);
			$return[] = $newid;
		}
		
		echo serialize($return);
	}
	
}
