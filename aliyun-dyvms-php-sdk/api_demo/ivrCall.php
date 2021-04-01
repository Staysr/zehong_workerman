<?php

ini_set("display_errors", "on");

require_once dirname(__DIR__) . '/api_sdk/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Dyvms\Request\V20170525\IvrCallRequest;
use Aliyun\Api\Dyvms\Request\V20170525\MenuKeyMap;

// 加载区域结点配置
Config::load();

/**
 * 交互式语音应答
 *
 * 语音服务API产品的DEMO程序，直接执行此文件即可体验语音服务产品API功能
 * (只需要将AK替换成开通了云通信-语音服务产品功能的AK即可)
 * 备注:Demo工程编码采用UTF-8
 */
function ivrCall() {
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
    $request = new IvrCallRequest();
    //必填-被叫显号
    $request->setCalledShowNumber("05344757036");
    //必填-被叫号码
    $request->setCalledNumber("1500000000");
    //选填-播放次数
    $request->setPlayTimes(3);



    //必填-语音文件ID或者tts模板的模板号,有参数的模板需要设置模板变量的值
    //$request->setStartCode("ebe3a2b5-c287-42a4-8299-fc40ae79a89f.wav");
    $request->setStartCode("TTS_713900000");
    $request->setStartTtsParams("{\"product\":\"aliyun\",\"code\":\"123\"}");
    $menuKeyMaps = array();

    $menuKeyMap1 = new MenuKeyMap();
    $menuKeyMap1->setKey("1");
    $menuKeyMap1->setCode("9a9d7222-670f-40b0-a3af.wav");
    $menuKeyMaps[] = $menuKeyMap1;

    $menuKeyMap2 = new MenuKeyMap();
    $menuKeyMap2->setKey("2");
    $menuKeyMap2->setCode("44e3e577-3d3a-418f-932c.wav");
    $menuKeyMaps[] = $menuKeyMap2;

    $menuKeyMap3 = new MenuKeyMap();
    $menuKeyMap3->setKey("3");
    $menuKeyMap3->setCode("TTS_71390000");
    $menuKeyMap3->setTtsParams("{\"product\":\"aliyun\",\"code\":\"123\"}");
    $menuKeyMaps[] = $menuKeyMap3;

    $request->setMenuKeyMaps($menuKeyMaps);

    //选填-等待用户按键超时时间，单位毫秒
    $request->setTimeout(3000);

    //选填-播放结束时播放的结束提示音,支持语音文件和Tts模板2种方式,但是类型需要与StartCode一致，即前者为Tts类型的，后者也需要是Tts类型的
    $request->setByeCode("TTS_71400007");

    //Tts模板变量替换JSON,当ByeCode为Tts时且Tts模板中带变量的情况下此参数必填
    $request->setByeTtsParams("{\"product\":\"aliyun\",\"code\":\"123\"}");

    //选填-外呼流水号
    $request->setOutId("yourOutId");

    //hint 此处可能会抛出异常，注意catch
    $response = $acsClient->getAcsResponse($request);

    return $response;
}

// 调用示例：
set_time_limit(0);
header("Content-Type: text/plain; charset=utf-8");

$response = ivrCall();
echo "交互式语音应答(ivrCall)接口返回的结果:\n";
print_r($response);
