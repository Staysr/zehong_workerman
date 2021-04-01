<?php

ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dyvms\Request\V20170525\BatchRobotSmartCallRequest;

// 加载区域结点配置
Config::load();

/**
 * 智能外呼批量任务呼叫
 *
 * 语音服务API产品的DEMO程序，直接执行此文件即可体验语音服务产品API功能
 * (只需要将AK替换成开通了云通信-语音服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
function batchRobotSmartCall() {
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

    $request = new BatchRobotSmartCallRequest();

    // 必填: 被叫显号,可在语音控制台中找到所购买的显号
    $request->setCalledShowNumber("06336760013");

    // 必填: 被叫号码
    $request->setCalledNumber("1004,1005");

    // 必填: 机器人ID
    $request->setDialogId("331234432");

    // 必填: 公司名称
    $request->setCorpName("阿里巴巴通信技术（北京）有限公司");

    // 必填: 任务名称
    $request->setTaskName("批量任务测试");

    // 选填: 早媒体语音识别标识，默认是false，使用的话设成true
    // $request->setEarlyMediaAsr("false");

    // 选填: 是否定时呼叫，设为true，则必须设置scheduleTime
    // $request->setScheduleCall("false");

    // 选填: 约定的呼叫时间
    // $request->setScheduleTime("2017-11-27 20:09:06");

    //hint 此处可能会抛出异常，注意catch
    $response = $acsClient->getAcsResponse($request);

    return $response;
}

// 调用示例：
set_time_limit(0);
header("Content-Type: text/plain; charset=utf-8");

$response = batchRobotSmartCall();
echo "智能外呼批量任务呼叫(batchRobotSmartCall)接口返回的结果:\n";
print_r($response);
