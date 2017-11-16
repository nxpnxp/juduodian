<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title> 首页</title>    
    <meta name="renderer" content="webkit">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" id="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/zui/1.7.0/css/zui.min.css">
    <link href="/Public/uploader/zui.uploader.min.css" rel="stylesheet">
    <script src="//cdnjs.cloudflare.com/ajax/libs/zui/1.7.0/lib/jquery/jquery.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/zui/1.7.0/js/zui.min.js"></script>
	<script src="/Public/uploader/zui.uploader.min.js"></script>
    <style>
     	
    </style>
</head>
<body>

<div id="uploaderExample" class="uploader" title="">
  <div class="file-list" data-drag-placeholder="请拖拽文件到此处"></div>
  <button type="button" class="btn btn-primary uploader-btn-browse"><i class="icon icon-cloud-upload"></i> 选择文件</button>
</div>


<script>

$('#uploaderExample').uploader({
    autoUpload: true,            // 当选择文件后立即自动进行上传操作
	max_file_size: '30mb',
    url: '/index.php?s=/Nn/file/upload',  // 文件上传提交地址
	mime_types: [
        {title: '图片', extensions: 'jpg, gif, png,jpeg,JPG,GIF,PNG,JPEG'},
        {title: '图标', extensions: 'ico'}
    ],
	onFileUploaded: function(file, responseObject) {
        
        var code = file.remoteData.result;
		
		var msg = file.remoteData.message;
		var ids = $('#uploaderExample').attr('title');
		if(code=='ok'){
			ids += file.remoteData.id + ',';
			$('#uploaderExample').attr('title',ids);
		}else{
		   alert(msg);
		   return false;
		}
    },
	onError:function(error){
	  alert(error.message)
	}
});
</script>
</body>
</html>