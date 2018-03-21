<?php
/**
 * Created by PhpStorm.
 * User: Shuke
 * Date: 2018/03/06星期二
 * Time: 15:22
 */

namespace BHOM\SMS;

use BHOM\SMS\Core\Profile\DefaultProfile;
use BHOM\SMS\Core\DefaultAcsClient;
use BHOM\SMS\Api\Sms\Request\V20170525\SendSmsRequest;
use BHOM\SMS\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use BHOM\SMS\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
// 加载区域结点配置
\BHOM\SMS\Core\Config::load();

class AliyunSMS
{
    static $acsClient = null;
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $region;
    protected $endPointName;

    public function __construct($accessKeyId, $accessKeySecret, $region="cn-hangzhou",$endPointName="cn-hangzhou"){
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->region = $region;
        $this->endPointName = $endPointName;
    }
    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    private function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = $this->accessKeyId; // AccessKeyId

        $accessKeySecret = $this->accessKeySecret; // AccessKeySecret

        // 暂时不支持多Region
        $region = $this->region;

        // 服务结点
        $endPointName = $this->endPointName;


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return stdClass
     */
    public function send($phonNumber,$tpcode,$tpparam,$sign) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($phonNumber);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($sign);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($tpcode);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($tpparam, JSON_UNESCAPED_UNICODE));

        // 可选，设置流水号
        $request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        $request->setSmsUpExtendCode("1234567");

        // 发起访问请求
        $acsResponse = $this->getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }

    /**
     * 批量发送短信
     * $phoneNumberArr = ["1500000000","1500000001"]
     * $signNameArr = ["云通信","云通信"]
     * $templateParamArr = [
     *           [
     *               "name" => "Tom",
     *               "code" => "123",
     *           ],
     *           [
     *               "name" => "Jack",
     *               "code" => "456",
     *           ]
     *       ]
     * @return stdClass
     */
    public function sendBatch($phoneNumberArr,$signNameArr,$templateCode,$templateParamArr) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendBatchSmsRequest();

        // 必填:待发送手机号。支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->setPhoneNumberJson(json_encode($phoneNumberArr, JSON_UNESCAPED_UNICODE));

        // 必填:短信签名-支持不同的号码发送不同的短信签名
        $request->setSignNameJson(json_encode($signNameArr, JSON_UNESCAPED_UNICODE));

        // 必填:短信模板-可在短信控制台中找到
        $request->setTemplateCode($templateCode);

        // 必填:模板中的变量替换JSON串,如模板内容为"亲爱的${name},您的验证码为${code}"时,此处的值为
        // 友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
        $request->setTemplateParamJson(json_encode($templateParamArr, JSON_UNESCAPED_UNICODE));

        // 可选-上行短信扩展码(扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段)
        // $request->setSmsUpExtendCodeJson("[\"90997\",\"90998\"]");

        // 发起访问请求
        $acsResponse = $this->getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }

    /**
     * 短信发送记录查询
     * @return stdClass
     */
    public function querySendDetails($phonNumber,$sendDate="20170718",$pageSize=10,$page=1,$BizId="") {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->setPhoneNumber($phonNumber);

        // 必填，短信发送日期，格式Ymd，支持近30天记录查询 20170718
        $request->setSendDate($sendDate);

        // 必填，分页大小
        $request->setPageSize($pageSize);

        // 必填，当前页码
        $request->setCurrentPage($page);

        // 选填，短信发送流水号
        if(!empty($BizId)){
            $request->setBizId($BizId);
        }

        // 发起访问请求
        $acsResponse = $this->getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }
}
