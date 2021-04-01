<?php

ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dyvms\Request\V20170525\ClickToDialRequest;

// 加载区域结点配置
Config::load();

/**
 * 点击拨号
 *
 * 语音服务API产品的DEMO程序，直接执行此文件即可体验语音服务产品API功能
 * (只需要将AK替换成开通了云通信-语音服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
function clickToDial() {
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

    //组装请求对象-具体描述见控制台-文档部分内容
    $request = new ClickToDialRequest();
    //必填-主叫显号
    $request->setCallerShowNumber("05344757036");
    //必填-主叫号码
    $request->setCallerNumber("1800000000");
    //必填-被叫显号
    $request->setCalledShowNumber("4001112222");
    //必填-被叫号码
    $request->setCalledNumber("1500000000");

    //可选-是否录音
    $request->setRecordFlag(true);

    //可选-是否开启实时ASR功能
    $request->setAsrFlag(true);

    //可选-ASR模型ID
    $request->setAsrModelId('2070aca1eff146f9a7bc826f1c3d4d33');

    //选填-外呼流水号
    $request->setOutId("yourOutId");

    //hint 此处可能会抛出异常，注意catch
    $response = $acsClient->getAcsResponse($request);

    return $response;
}

// 调用示例：
set_time_limit(0);
header("Content-Type: text/plain; charset=utf-8");

$response = clickToDial();
echo "点击拨号(clickToDial)接口返回的结果:\n";
print_r($response);
