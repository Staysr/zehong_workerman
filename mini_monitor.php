<?php

/**
 * 微信小程序设备列表Websock服务
 */
use Workerman\Worker;
use Workerman\lib\Timer;
require_once __DIR__ . '/Usemysql.php';
use Workerman\Usemysql;
require_once __DIR__ . '/Autoloader.php';

define('HEARTBEAT_TIME', 20);

// 证书最好是申请的证书
$context = array(
    // 更多ssl选项请参考手册 http://php.net/manual/zh/context.ssl.php
    'ssl' => array(
        // 请使用绝对路径
        'local_cert'                 => 'ssl_wx.zhkjgf.com_nginx/2731211_wx.zhkjgf.com.pem', // 也可以是crt文件
        'local_pk'                   => 'ssl_wx.zhkjgf.com_nginx/2731211_wx.zhkjgf.com.key',
        'verify_peer'                => false,
        // 'allow_self_signed' => true, //如果是自签名证书需要开启此选项
    )
);
// 这里设置的是websocket协议（端口任意，但是需要保证没被其它程序占用）

// 创建一个Worker监听2346端口，使用websocket协议通讯
$worker = new Worker("websocket://0.0.0.0:2353",$context);

// 启动4个进程对外提供服务
$worker->count = 10;
$worker->transport = 'ssl';

$worker->onMessage = function($connection, $data)
{
    $connection->lastMessageTime = time();
    senddata($connection, $data);
    $connection->timer_id = Timer::add(10, function()use($connection,$data)
    {
        senddata($connection, $data);
    });

};

function senddata($connection,$data)
{
    if(empty($data)){
        $connection->send(json_encode(['code'=>401,'msg' => 'no data']));
    }
   
    $mysqli = new Usemysql();
    $devicenums = "'".implode("','",json_decode($data,true))."'";
    $sql = "SELECT d.devicenum,d.status,d.nd,s.status_name FROM devices AS d LEFT JOIN status AS s ON s.id=d.status LEFT JOIN status AS ss ON ss.id=d.zbd WHERE d.devicenum IN ({$devicenums}) ORDER BY create_time DESC";

    $rows = $mysqli->selectArray($sql);
    
    $connection->send(json_encode($rows));
}

$worker->onWorkerStart = function($worker) {
    $time_now = time();
    foreach($worker->connections as $connection) {
        // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
        if (empty($connection->lastMessageTime)) {
            $connection->lastMessageTime = $time_now;
            continue;
        }
        // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
        if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
            $connection->close();
        }
    }
};

$worker->onClose = function($connection) {
    if(isset($connection->timer_id)){
        Timer::del($connection->timer_id);
    }
};

Worker::runAll();

?>