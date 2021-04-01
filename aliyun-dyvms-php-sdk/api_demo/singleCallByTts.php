<?php

ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dyvms\Request\V20170525\SingleCallByTtsRequest;

// 加载区域结点配置
Config::load();

/**
 * 文本转语音外呼
 *
 * 语音服务API产品的DEMO程序，直接执行此文件即可体验语音服务产品API功能
 * (只需要将AK替换成开通了云通信-语音服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
function singleCallByTts($cphone,$tts_code='TTS_160570466',$data='') {
    //产品名称:云通信语音服务API产品,开发者无需替换
    $product = "Dyvmsapi";

    //产品域名,开发者无需替换
    $domain = "dyvmsapi.aliyuncs.com";

    // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
    $accessKeyId = "LTAI2xiZNF3iV2aV"; // AccessKeyId

    $accessKeySecret = "bprEWwn1M0xgglRQCQEMYSPiYctDk4"; // AccessKeySecret


    // 暂时不支持多Region
    $region = "cn-hangzhou";

    // 服务结点
    $endPointName = "cn-hangzhou";

    //初始化acsClient,暂不支持region化
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

    // 增加服务结点
    DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

    // 初始化AcsClient用于发起请求
    $acsClient = new DefaultAcsClient($profile);

    //组装请求对象-具体描述见控制台-文档部分内容
    $request = new SingleCallByTtsRequest();
    //必填-被叫显号
    $request->setCalledShowNumber("031166859686");
    //必填-被叫号码
    $request->setCalledNumber($cphone);
    //必填-Tts模板Code
    $request->setTtsCode($tts_code);
    //选填-Tts模板中的变量替换JSON,假如Tts模板中存在变量，则此处必填
    $request->setTtsParam(json_encode($data,JSON_UNESCAPED_UNICODE));
    //选填-音量
    // $request->setVolume(100);
    //选填-播放次数
    $request->setPlayTimes(1);
    //选填-外呼流水号
    // $request->setOutId("yourOutId");

    //hint 此处可能会抛出异常，注意catch
    $response = $acsClient->getAcsResponse($request);

    return $response;
}


