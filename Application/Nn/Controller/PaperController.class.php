<?php
namespace Nn\Controller;
//use Think\Controller;
class PaperController extends HomeController {
    
	//生成红包海报
    public function index(){
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$uid = $user['id'];
		$sid = I('shopid');
		$image = new \Think\Image(); 
		$thumb = "./Uploads/Avatar/".$uid.".png";
		if(file_exists($thumb)){
			$headpic = "./Public/paper/headpic".$uid.".png";
			$image->open($thumb)->thumb(120, 120)->save($headpic);
			$circle = $this->test($headpic);
		}else{
			$circle = "./Public/logo.png";
		}
		$image->open('./Public/redpackbg.jpg');
		$location=array(180,120);
		$paper = "./Public/paper/water".$uid.$sid.".png";
		$image->water($circle,$location,100)->save($paper);
		if($circle != "./Public/logo.png"){
			@unlink($circle);
		}
		@unlink($headpic);
		$nickname = $user['nickname']."抢得红包";
		$money = M("WxuserYuelog")->where(array('uid'=>$uid))->order("time desc")->find();
		$money = "￥".$money['fee'];
		$image->open($paper)->text($nickname,'./Public/2.ttf',20,'#fff100',array(100,260))->save($paper); 
		$image->open($paper)->text($money,'./Public/2.ttf',40,'#fff100',array(180,310))->save($paper);
		$image->open($paper)->text('【可提现】','./Public/2.ttf',20,'#fff100',array(180,370))->save($paper);
        $image->open($paper)->text("长按识别二维码，一起抢红包",'./Public/2.ttf',18,'#ffffff',array(80,420))->save($paper);
		$$uid = $this->getQrcode($sid,$uid);
		$image->open($paper);
		$location=array(160,460);
		$image->water($$uid,$location,100)->save($paper);
		@unlink($$uid);
		echo $paper;
		
	}

   //生成红包二维码
   public function getQrcode($sid=0,$uid=0){
	  $data = "http://jdd.vipin.net.cn/nn.php?s=/Shop/detail.html&id=".$sid."&pid=".$uid;
	  include('./Public/phpqrcode/phpqrcode.php');  
	  $filename = "./Public/paper/shop".$sid.".png";  //  生成的文件名  
	  $errorCorrectionLevel = 'L';  // 纠错级别：L、M、Q、H  
	  $matrixPointSize = 4; // 点的大小：1到10  
	  \QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);   
	  return "./Public/paper/shop".$sid.".png";
   }

	//生成推广海报
	public function tgpaper(){
		$openid = $this->openid;
		$user = M('WxuserCode')->where(array('openid'=>$openid))->find();
		$uid = $user['id'];
		$image = new \Think\Image(); 
		$thumb = "./Uploads/Avatar/".$uid.".png";
		if(file_exists($thumb)){
			$headpic = "./Public/paper/headpic".$uid.".png";
			$image->open($thumb)->thumb(120, 120)->save($headpic);
			$circle = $this->test($headpic);
		}else{
			$circle = "./Public/logo.png";
		}
		$image->open('./Public/redpackbg.jpg');
		$location=array(180,120);
		$paper = "./Public/paper/waterp".$uid.".png";
		$image->water($circle,$location,100)->save($paper);
		if($circle != "./Public/logo.png"){
			@unlink($circle);
		}
		@unlink($headpic);
		$nickname = $user['nickname'].'邀您';
		$image->open($paper)->text($nickname,'./Public/2.ttf',20,'#fff100',array(100,260))->save($paper); 
		$image->open($paper)->text('一起抢红包','./Public/2.ttf',40,'#fff100',array(180,310))->save($paper);
		$image->open($paper)->text("长按识别二维码，一起抢红包",'./Public/2.ttf',18,'#ffffff',array(80,420))->save($paper);
		$$uid = $this->getQrcode1($uid);
		$image->open($paper);
		$location=array(160,460);
		$image->water($$uid,$location,100)->save($paper);
		@unlink($$uid);
		echo $paper;
	}
	
   public function getQrcode1($uid=0){
	  $data = "http://jdd.vipin.net.cn/nn.php?s=/Shop/index.html&pid=".$uid;
	  include('./Public/phpqrcode/phpqrcode.php');  
	  $filename = "./Public/paper/member".$uid.".png";  //  生成的文件名  
	  $errorCorrectionLevel = 'L';  // 纠错级别：L、M、Q、H  
	  $matrixPointSize = 4; // 点的大小：1到10  
	  \QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);   
	  return "./Public/paper/member".$uid.".png";
   }
    //生成圆形图片
	public  function test($url,$path='./Public/paper/'){  
	  $w = 120;  $h=120; // original size  
	  $original_path= $url;  
	  $dest_path = $path.uniqid().'.png';  
	  $src = imagecreatefromstring(file_get_contents($original_path));  
	  $newpic = imagecreatetruecolor($w,$h);  
	  imagealphablending($newpic,false);  
	  $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);  
	  $r=$w/2;
	  for($x=0;$x<$w;$x++)  
		  for($y=0;$y<$h;$y++){  
			  $c = imagecolorat($src,$x,$y);  
			  $_x = $x - $w/2;  
			  $_y = $y - $h/2;  
			  if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){  
				  imagesetpixel($newpic,$x,$y,$c);  
			  }else{  
				  imagesetpixel($newpic,$x,$y,$transparent);  
			  }  
		  }  
	  imagesavealpha($newpic, true);  
	  imagepng($newpic, $dest_path);  
	  imagedestroy($newpic);  
	  imagedestroy($src);  
	 // unlink($url);  
	  return $dest_path;  
    }  

}