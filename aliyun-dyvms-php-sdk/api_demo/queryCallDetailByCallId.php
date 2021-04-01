<?php

ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dyvms\Request\V20170525\QueryCallDetailByCallIdRequest;

// 加载区域结点配置
Config::load();

/**
 * 通过呼叫ID获取呼叫记录
 *
 * 语音服务API产品的DEMO程序，直接执行此文件即可体验语音服务产品API功能
 * (只需要将AK替换成开通了云通信-语音服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
function queryCallDetailByCallId() {
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

    $request = new QueryCallDetailByCallIdRequest();

    //组装请求对象-CallId从上次呼叫调用的返回值中获取
    $request->setCallId("113853585007^100675005007");

    // 必填: 设置你需要查询的时间，会查询对应那一天的记录，注意：跨天情况可以判断一下昨天的记录
    $request->setQueryDate(time().'000');

    // 必填: 设置对应的产品类型
    // 语音通知(11000000300006)
    // 语音验证码(11010000138001)
    // IVR(11000000300005)
    // 点击拨号(11000000300004)
    // SIP(11000000300009)
    $request->setProdId("11000000300004");

    // 注意: 此处可能会抛出异常，注意catch
    $response = $acsClient->getAcsResponse($request);

    return $response;
}

// 调用示例：
set_time_limit(0);
header("Content-Type: text/plain; charset=utf-8");

$response = queryCallDetailByCallId();
echo "通过呼叫ID获取呼叫记录(queryCallDetailByCallId)接口返回的结果:\n";
print_r($response);
