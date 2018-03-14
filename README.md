# Aliyun SMS PHP SDK composer版 版本：V20170525

# 安装 
```php
composer require blackhilloldmonster/aliyun_sms_php
```
# 使用
```php
use BHOM\SMS\AliyunSMS;
.....
.....
.....
$accessKeyId = "";
$accessKeySecret = "";
$region = "";
$endPointName = "";
$phonNumber = "13900000000";
$tpcode = "SMS_177777";
$tpparam = ["code"=>"992929"];
$sign = "签名";
$sms = new AliyunSMS($accessKeyId, $accessKeySecret,$region,$endPointName);
$status = $sms->send($phonNumber,$tpcode,$tpparam,$sign);
```