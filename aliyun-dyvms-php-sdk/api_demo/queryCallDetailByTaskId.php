<?php

ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dyvms\Request\V20170525\QueryCallDetailByTaskIdRequest;

// 加载区域结点配置
Config::load();

/**
 * 通过任务ID获取机器人话单详情
 *
 * 语音服务API产品的DEMO程序，直接执行此文件即可体验语音服务产品API功能
 * (只需要将AK替换成开通了云通信-语音服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
function queryCallDetailByTaskId() {
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

    $request = new QueryCallDetailByTaskIdRequest();

    // 接口返回的任务ID
    $request->setTaskId("4001112222");

    // 被叫号码，目前只支持单个
    $request->setCallee("13700000000");

    // Unix时间戳，会查询这个时间点对应那一天的记录（单位毫秒）
    $request->setQueryDate(time().'000');

    // 注意: 此处可能会抛出异常，注意catch
    $response = $acsClient->getAcsResponse($request);

    return $response;
}

// 调用示例：
set_time_limit(0);
header("Content-Type: text/plain; charset=utf-8");

$response = queryCallDetailByTaskId();
echo "通过任务ID获取机器人话单详情(queryCallDetailByTaskId)接口返回的结果:\n";
print_r($response);
