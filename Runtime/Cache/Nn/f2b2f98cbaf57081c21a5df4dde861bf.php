<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title> 首页</title>    
    <meta name="renderer" content="webkit">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" id="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" type="text/css" href="/Public/Nn/css/layui.css" media="all">	
    <link rel="stylesheet" type="text/css" href="/Public/Nn/css/iconfont.css">
     <style>
     	.layui-carousel img{ width: 100%;height: 150px;}
     	.subscribe{ 
     		padding: 10px 0;
     		color: red;
     		font-size: 15px;
     		text-align: center;
     		width: 90%;
     		margin: 0 auto;
     	}
     	.cate{
     		padding: 10px 0;
     		font-size: 13px;     		
     		text-align: center;
     	}
     	.cate img{ width: 30px;height: 30px; }
     	
     	
     	.morendian,
     	.hongbaodian{width: 90%;margin: 0 auto;}
     	.morendian img,
     	.hongbaodian img{width: 50px;height: 50px;}
     	.morendian .layui-row,
     	.hongbaodian .layui-row{
     		line-height:50px;
     		height: 50px;
     		margin-bottom: 10px;
     		padding-bottom: 10px;
     		border-bottom: 1px dashed #F0F0F0;
     	}
     	.text-right{text-align:right;}
     	
     </style>
</head>
<body>
	<?php if($user["subscribe"] != 1): ?><div class="subscribe">您未关注，请点击关注</div><?php endif; ?>
	
	<div class="layui-carousel" id="lunbo" lay-filter="lunbo">
	  <div carousel-item="">
	    <div><img src="https://openfile.meizu.com/group1/M00/02/34/Cgbj0FnJ9RmAOZ7wAALdsj0_6fA107.jpg"></div>
	    <div><img src="https://openfile.meizu.com/group1/M00/02/2E/Cgbj0FnHjJuAXp1zAASEs9u1lRY669.jpg"></div>
	  </div>
	</div> 
	
	<div class="layui-row">
		<?php if(is_array($cates)): foreach($cates as $key=>$vo): ?><div class="cate layui-col-xs3" cateid="<?php echo ($vo["id"]); ?>" >
				<div><img src="<?php echo ($vo["path"]); ?>" /></div>
		    	<div><?php echo ($vo["title"]); ?></div>
		    </div><?php endforeach; endif; ?>
  	</div>
	
	<div class="layui-tab">
	  <ul class="layui-tab-title">
	    <li class="layui-this">推荐店铺</li>
	    <li>附近店铺</li>
	  </ul>
	  <div class="layui-tab-content">
	    <div class="layui-tab-item layui-show">
	      	
	      	<div class="morendian">
	      		<?php if(is_array($morens)): foreach($morens as $key=>$vo): ?><div class="layui-row open" data-id="<?php echo ($vo["id"]); ?>">
				    <div class="layui-col-xs3"><img src="<?php echo ($vo["path"]); ?>" /></div>
			    	<div class="layui-col-xs6"><?php echo ($vo["title"]); ?></div>
			    	<div class="layui-col-xs3 text-right" data-lon="<?php echo ($vo["longitude"]); ?>" data-lat="<?php echo ($vo["latitude"]); ?>"> </div>
				</div><?php endforeach; endif; ?>			 			  
			</div>
			
	    </div>
	    <div class="layui-tab-item">
	    	
	    	<div class="hongbaodian">
	      		<?php if(is_array($morens)): foreach($morens as $key=>$vo): ?><div class="layui-row open" data-id="<?php echo ($vo["id"]); ?>">
				    <div class="layui-col-xs3"><img src="<?php echo ($vo["path"]); ?>" /></div>
			    	<div class="layui-col-xs6"><?php echo ($vo["title"]); ?></div>
			    	<div class="layui-col-xs3 text-right" data-lon="<?php echo ($vo["longitude"]); ?>" data-lat="<?php echo ($vo["latitude"]); ?>"> </div>
				</div><?php endforeach; endif; ?>
			</div>
			
	    </div>
	  </div>
	</div>
	
	<style>
 	.footer-up{height: 60px;}
 	.footers{
 		width: 100%;
 		height: 60px;
 		position: fixed;
 		bottom: 0;
 		border-top: 1px solid #ccc;
 		background-color: #fff;
 	}
 	.footer{
 		margin-top: 5px;
 		width: 33.33%;	
 		text-align: center;
 	}     	
 	.iconfont{font-size: 30px !important;}
 	.out{
	     font-size:0px;
	}
	.in{
	     display:inline-block;
	     vertical-align:top;
	}
	.in{
	    *display:inline;
	}
	.footer span{
		 font-size: 13px;
		 display: block;
	}
	.footer-on{
		color: #ff3366;
	}
</style>
 
	
	
<div class="footer-up"></div>
<div class="footers out">
	<!--<?php echo (MODULE_NAME); ?>
	<?php echo (CONTROLLER_NAME); ?>
	<?php echo (ACTION_NAME); ?>-->
	<div nid="1" class="footer in <?php if((CONTROLLER_NAME) == "Index"): ?>footer-on<?php endif; ?>"><i class="iconfont icon-zhuye1"></i><span>首页</span></div>
	<div nid="2" class="footer in <?php if((CONTROLLER_NAME) == "Shop"): ?>footer-on<?php endif; ?>"><i class="iconfont icon-dianpu-copy"></i><span>创建店铺</span></div>
	<div nid="3" class="footer in <?php if((CONTROLLER_NAME) == "Member"): ?>footer-on<?php endif; ?>"><i class="iconfont icon-huiyuan"></i><span>个人中心</span></div>
</div>

	
</body>
<script src="//res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
<script type="text/javascript" src="/Public/Nn//layui.js"></script>
<script>
	// 微信JSSDK异常处理
	wx.error(function(e){
    	alert('wxjssdk_error');
    });
    // 注入JSSDK配置参数，默认开启所有接口权限
    wx.config(<?php echo ($options); ?>);
    // 当JSSDK初始化完成后，再执行相关操作
    wx.ready(function(){
        // 这里就可以调用 wx 的jssdk的操作了
        
        wx.getLocation({
		    type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
		    success: function (res) {
		        var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
		        var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
		        var speed = res.speed; // 速度，以米/每秒计
		        var accuracy = res.accuracy; // 位置精度
		        //alert(latitude+'--'+longitude);
		        calc(latitude,longitude);
		        
		    }
		});
		
		function calc(latitude,longitude){
			layui.use(['layer','jquery'], function(){
				var $ = layui.$ //重点处
	  			,layer = layui.layer;
	  			
	  			var lat1 = latitude;
	  			var lng1 = longitude;
	  			
	  			$.ajax({
		        	type:"post",
		        	url:"<?php echo U('Member/savelatlon');?>",
		        	data:{lat:lat1,lon:lng1}
		        });
	  			
	  			var EARTH_RADIUS = 6378137.0;    //单位M
			    var PI = Math.PI;
			     
				$('.morendian>div').each(function(i,o){
					var obj = $(o).find('.text-right');
					var lon = obj.attr('data-lon');
					var lat = obj.attr('data-lat');
					//layer.msg(lon+' - '+lat);
					var res = getGreatCircleDistance(lat1,lng1,lat,lon);
					res = res/1000;
					res = res.toFixed(2);
					obj.html(res+'km');
				});
				
				
			    function getRad(d){
			         return d*PI/180.0;
			    }
			     
			     /**
			      * caculate the great circle distance
			      * @param {Object} lat1
			      * @param {Object} lng1
			      * @param {Object} lat2
			      * @param {Object} lng2
			      */
			     function getGreatCircleDistance(lat1,lng1,lat2,lng2){
			         var radLat1 = getRad(lat1);
			         var radLat2 = getRad(lat2);
			         
			         var a = radLat1 - radLat2;
			         var b = getRad(lng1) - getRad(lng2);
			         
			         var s = 2*Math.asin(Math.sqrt(Math.pow(Math.sin(a/2),2) + Math.cos(radLat1)*Math.cos(radLat2)*Math.pow(Math.sin(b/2),2)));
			         s = s*EARTH_RADIUS;
			         s = Math.round(s*10000)/10000.0;
			                
			         return s;
			     }
				
			});
		}
        
    });
</script> 
<script>
	layui.use(['layer','jquery'], function(){
		
		//弹出层
	  	var $ = layui.$ //重点处
	  	,layer = layui.layer;
	  	$('.subscribe').click(function(){
	  		layer.open({
			  type: 1, 
			  title: false, // false  '请长按识别二维码'
			  content: '<img src="/Public/erweima.jpg" style="width:250px;height:250px;"/>'
			});
	  	});
	  	
	  	$('.cate').click(function(){
			var cateid = $(this).attr('cateid');
			var url = "<?php echo U('Category/index');?>"+'&cateid='+cateid;
			window.location.href = url;
		});
	  	
	  	$('.footer').click(function(){
	  		$(this).addClass('footer-on').siblings().removeClass('footer-on');
			var nid = $(this).attr('nid');
			if(nid=='1'){
				var url = "<?php echo U('Index/index');?>";
			}
			if(nid=='2'){
				var url = "<?php echo U('Shop/index');?>";
			}
			if(nid=='3'){
				var url = "<?php echo U('Member/index');?>";
			}
			
			window.location.href = url;
		});
		
		$('.open').click(function(){
			var id = $(this).attr('data-id');
			var url = "<?php echo U('Shop/detail');?>"+'&id='+id;
			window.location.href = url;
		});
		
	});
	
	layui.use(['carousel','element'], function(){
	
		//轮播
		var carousel = layui.carousel;
		carousel.render({
		    elem: '#lunbo',
		    width: '100%',
		    height: '150px',
		    arrow: 'none',
		    indicator: 'none',
		    interval: '3000'
		});
	  
	  	//选项卡
	   	var element = layui.element;
	  	//…
	  	
	});
	
</script>
</html>