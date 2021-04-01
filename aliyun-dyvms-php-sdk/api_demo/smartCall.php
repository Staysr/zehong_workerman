<?php

ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dyvms\Request\V20170525\SmartCallRequest;

// 加载区域结点配置
Config::load();

/**
 * 智能外呼
 *
 * 语音服务API产品的DEMO程序，直接执行此文件即可体验语音服务产品API功能
 * (只需要将AK替换成开通了云通信-语音服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
function smartCall() {
    //产品名称:云通信语音服务API产品,开发者无需替换
    $product = "Dyvmsapi";

    //产品域名,开发者无需替换
    $domain = "dyvmsapi.aliyuncs.com";

    // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
    $accessKeyId = "yourAccessKeyId"; // AccessKeyId

    $accessKeySecret = "yourAccessKeySecret"; // AccessKeySecret


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

    $request = new SmartCallRequest();

    // 必填: 被叫显号,可在语音控制台中找到所购买的显号
    $request->setCalledShowNumber("06336760013");

    // 必填: 被叫号码
    $request->setCalledNumber("1005");

    // 必填: 语音文件ID
    $request->setVoiceCode("3355eedd-3706-4f75-bff9-e645e88e1730.wav");

    // 可选: 外部扩展字段
    $request->setOutId("yourOutId");

    // 选填：放音速度
    $request->setSpeed(1);

    // 选填: 音量
    $request->setVolume(10);

    // 选填: 静音时长
    $request->setMuteTime(10000);

    // 选填: 停顿时长
    $request->setPauseTime(800);

    // 选填: 开场放音文件是否可打断，默认为true，打断
    $request->setActionCodeBreak(false);

    // 选填：ASR模型ID
    $request->setAsrModelId("2070aca1eff146f9a7bc826f1c3d4d31");

    //hint 此处可能会抛出异常，注意catch
    $response = $acsClient->getAcsResponse($request);

    return $response;
}

// 调用示例：
set_time_limit(0);
header("Content-Type: text/plain; charset=utf-8");

$response = smartCall();
echo "智能外呼(smartCall)接口返回的结果:\n";
print_r($response);
