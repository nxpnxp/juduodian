<?php
require_once(dirname(dirname(__FILE__)).'/mns-autoloader.php');

use AliyunMNS\Client;
use AliyunMNS\Topic;
use AliyunMNS\Constants;
use AliyunMNS\Model\MailAttributes;
use AliyunMNS\Model\SmsAttributes;
use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\PublishMessageRequest;

class PublishBatchSMSMessageDemo
{
   

   public function run($mobile = '17082230932',$code = '12345')
    {
    	
        /**
         * Step 1. 初始化Client
         */
        $this->endPoint = "http://1807166702621080.mns.cn-hangzhou.aliyuncs.com/"; 
        $this->accessId = "LTAI1n1S3DyaZOdj";   
        $this->accessKey = "k2GkEAeRE1CMjv5lC3g9vMrWXwJ75O";
        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
		
		
        /**
         * Step 2. 获取主题引用
         */
        $topicName = "sms.topic-cn-hangzhou";//YourTopicName
        $topic = $this->client->getTopicRef($topicName);
				
        /**
         * Step 3. 生成SMS消息属性
         */
        $batchSmsAttributes = new BatchSmsAttributes("注册", "SMS_34365182");
        $batchSmsAttributes->addReceiver($mobile, array("code" => $code, "product" => " [聚多店] "));
        $messageAttributes = new MessageAttributes(array($batchSmsAttributes));
        /**
         * Step 4. 设置SMS消息体（必须）
         *
         * 注：目前暂时不支持消息内容为空，需要指定消息内容，不为空即可。
         */
        $messageBody = "smsmessage";
        /**
         * Step 5. 发布SMS消息
         */
        $request = new PublishMessageRequest($messageBody, $messageAttributes);
		
		echo ' ';
		
        try
        {
            $res = $topic->publishMessage($request);
			
			//var_dump($res);
            //var_dump($res->isSucceed());
            //var_dump($res->getMessageId());
        }
        catch (MnsException $e)
        {
            var_dump($e);
        }
    }
}

//http://jdd.vipin.net.cn/aliyunsms/AliyunMNS/Sms.php?c=17082230932&i=22111
$mobile = $_GET['c'];
$code = $_GET['i'];

$instance = new PublishBatchSMSMessageDemo();
$instance->run($mobile,$code);

?>
