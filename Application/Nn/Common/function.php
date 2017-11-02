<?php

/**
 * 获取微信操作对象（单例模式）
 * @staticvar array $wechat 静态对象缓存对象
 * @param type $type 接口名称 ( Card|Custom|Device|Extend|Media|Oauth|Pay|Receive|Script|User ) 
 * @return \Wehcat\WechatReceive 返回接口对接
 */
function & load_wechat($type = '') {
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
    	// 定义微信公众号配置参数（这里是可以从数据库读取的哦）
       $options = array(
            'token'           => 'FU5zfr54p11EpzyRP1RZVuFypYN1yaPe', // 填写你设定的key
            'appid'           => 'wxaae12993a4d3e5f8', // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'       => '943bf485f4a4cdf32b27d7aa28829960', // 填写高级调用功能的密钥
            'encodingaeskey'  => 'gfiHc079up1aEoGI6Js2FRWanABLGUa9XCDZWo4HGBb', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
            'mch_id'          => '10018861', // 微信支付，商户ID（可选）
            'partnerkey'      => 'vipin521vipin521vipin521vipin905', // 微信支付，密钥（可选）
            //'ssl_cer'         => 'MIIEZzCCA9CgAwIBAgICTJQwDQYJKoZIhvcNAQEFBQAwgYoxCzAJBgNVBAYTAkNOMRIwEAYDVQQIEwlHdWFuZ2RvbmcxETAPBgNVBAcTCFNoZW56aGVuMRAwDgYDVQQKEwdUZW5jZW50MQwwCgYDVQQLEwNXWEcxEzARBgNVBAMTCk1tcGF5bWNoQ0ExHzAdBgkqhkiG9w0BCQEWEG1tcGF5bWNoQHRlbmNlbnQwHhcNMTQwOTMwMDkwMDQ0WhcNMjQwOTI3MDkwMDQ0WjCBmDELMAkGA1UEBhMCQ04xEjAQBgNVBAgTCUd1YW5nZG9uZzERMA8GA1UEBxMIU2hlbnpoZW4xEDAOBgNVBAoTB1RlbmNlbnQxDjAMBgNVBAsTBU1NUGF5MS0wKwYDVQQDFCTllK/lk4Hnp5HmioDvvIjlpKnmtKXvvInmnInpmZDlhazlj7gxETAPBgNVBAQTCDEwMDE4ODYxMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtsjluphg9HeuaY2U//811103NfoQzZRhkIPkxyXAyvfTe32bfjlP1lUlsitPNZpg+XZQMsZJidEsnZCnwid4XS7wy7E3ySS7wBVcp+I+N/shz+sjPCGBlPbtvEQGnGs5+1T6We1SIOWB6rrET2TpQ6oTJfq8nshq+rc+mElJbd9tjN3v2qWHvK9J6/V/XxKP5Eu9/CbwRbPePkWpxHZ6roD68CDHZWDMcI4zVNkKGdUkjfK8atwAiTnqCfzTbKTaGtaCpCSF8unhTzev599bG4Ajr3wRt8OfmWttunIdBHhTZ1RmPiBgzkGDXV9ji+yvpCn4XW0PvD2XKU1QOKV5xwIDAQABo4IBRjCCAUIwCQYDVR0TBAIwADAsBglghkgBhvhCAQ0EHxYdIkNFUy1DQSBHZW5lcmF0ZSBDZXJ0aWZpY2F0ZSIwHQYDVR0OBBYEFHBk5AqB4tfNb9MYZHHQ7pFr/lZqMIG/BgNVHSMEgbcwgbSAFD4FJvYiYrQVW4jNZH6w1GKn5YZ0oYGQpIGNMIGKMQswCQYDVQQGEwJDTjESMBAGA1UECBMJR3Vhbmdkb25nMREwDwYDVQQHEwhTaGVuemhlbjEQMA4GA1UEChMHVGVuY2VudDEMMAoGA1UECxMDV1hHMRMwEQYDVQQDEwpNbXBheW1jaENBMR8wHQYJKoZIhvcNAQkBFhBtbXBheW1jaEB0ZW5jZW50ggkAu1SXK7wA6FcwDgYDVR0PAQH/BAQDAgbAMBYGA1UdJQEB/wQMMAoGCCsGAQUFBwMCMA0GCSqGSIb3DQEBBQUAA4GBAI5T4R60HSliq0F5QHwKKT146o6lV45GQ33pxd/MbCwg9/PhhHtWLSr4I3OBU1JEu9GrZrqZNhzDm4OEtjHqmxytSIBeA/Z3xIUnSE4/qfpPwK6CCCqrMc0mjeG6nzQMskEckYvuAYju3qQ71QhFq0sV98O3QHIK0/zywC//ynxr', // 微信支付，双向证书（可选，操作退款或打款时必需）
      		//'ssl_key'         => 'MIIEwAIBADANBgkqhkiG9w0BAQEFAASCBKowggSmAgEAAoIBAQC2yOW6mGD0d65pjZT//zXXXTc1+hDNlGGQg+THJcDK99N7fZt+OU/WVSWyK081mmD5dlAyxkmJ0SydkKfCJ3hdLvDLsTfJJLvAFVyn4j43+yHP6yM8IYGU9u28RAacazn7VPpZ7VIg5YHqusRPZOlDqhMl+ryeyGr6tz6YSUlt322M3e/apYe8r0nr9X9fEo/kS738JvBFs94+RanEdnqugPrwIMdlYMxwjjNU2QoZ1SSN8rxq3ACJOeoJ/NNspNoa1oKkJIXy6eFPN6/n31sbgCOvfBG3w5+Za226ch0EeFNnVGY+IGDOQYNdX2OL7K+kKfhdbQ+8PZcpTVA4pXnHAgMBAAECggEBAKOdoUYuMFuk5hLGEaI1qNRnNIy0k2XydIMQDTHWsYT94eigvxd15elNvriF3Bl3X2buaUuKAznCa9V/Lyu0eSGwawtqTy1WHFoMxSvicR9bRSEAskGZHI1jm9ryaZiKwxQfNRpaPTIFPw4GsMMA6W0QSKOuljjr3hcfAIEA2SHnYkScdjVCFziZCC8p8u0xVZIRPVcl0KrzUmPdZ08+PsYIU+5B982EIPcHSKP16lQCBiCZnjitFDOvSOGZzMRh4vKq0j/C3RJsH/ck/6Mg3dRAZKV/CYFOk4F8aRrn/FyTJmkMtD5yuzvXy0avMSSHVBnkhE1bOMukx969z9+5WkECgYEA4zOg/bV7aq2TgVx9kvv3vVyNCqeVv71G9ANtHLGQzlamcmw1+ZBv447Ng8wX1YB3VMKpH4IN31dtW0NZ2cxlIJ7VYpM2EHEDMvoiTOfsCwCj4WVkBvr5q+xJXVEOyKgMZ3ZWeUCvfMpdxH9x+JjaM6TbH3a5jhXzbngrrZtOQuUCgYEAzfQArYDWsT48oYeIz5/dnKXypnJVCg7ZW+//kT4pg2jBHBbCvCtT7EoQkBDF5BYwI6NZ0Q5R3Hyq0Gd1Wp2i8ZDkBfESFOviezV3vEQPGP5Ym1sUT5RuiOJfST/N/B8wvCpNOD5hGo1I66n4NLeMJTQ62fC4/tgeJ/p0NHtv4zsCgYEAtokrRcqBrlJoGN1jMiznfCaYVkAP6q06DR+XkR8D1RL+xR01PB0UF8IKUWIun+SHevyt/ddyJ8bI4DK7RGWXtd6GV50oKYtyY4lc92a5WJmaEKNx+r22y66ZvrQ56XJCDPmhPed/VgYL0awGuBCt9iHzvlKXf5UZL96yUNdeyBUCgYEAtSiPPA7bSlbzYCZm+jKl7bevBCgHk2xSV2N5SxoBzBxl2L5QltIJ5QuVHBQU7bdyqrZyN8i/yxFB80U93fJRNOl9GZjejdabhkMWylyXZyW7bRQlfoaS4Ac3R6g/GSmaZblT6Ug9g+yJGvqMYFqfhM43giMTpt8VT5cnhk6ZsacCgYEAz6q9OqABksNCB27GiJZdk5DWjmlkOTCwjThBguWRQdwIuhncoS2EjZGM0PVg2172CfFPrHxC2/jVfXJ/1b5nF0P6mzg+ZLcAvOlYVAIuQmLojnDjth5RUj8nVEa2Q07Q8SThRzolydbUPPAmidouN1lzjj8ejN/4poN1frWJdjc=', // 微信支付，双向证书（可选，操作退款或打款时必需）
            'ssl_cer' => './wechat/cert/apiclient_cert.pem',
            'ssl_key' => './wechat/cert/apiclient_key.pem',
            'cachepath'       => '', // 设置SDK缓存目录（可选，默认位置在Wechat/Cache下，请保证写权限）
        );
        \Wechat\Loader::config($options);
        $wechat[$index] = \Wechat\Loader::get($type);
    }
    return $wechat[$index];
}