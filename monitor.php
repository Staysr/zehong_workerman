<?php
use Workerman\Worker;
use Workerman\lib\Timer;
require_once __DIR__ . '/DataOut.php';
use Workerman\UseMysqli;
require_once __DIR__ . '/Autoloader.php';

define('HEARTBEAT_TIME', 20);
// 创建一个Worker监听2346端口，使用websocket协议通讯
$worker = new Worker("websocket://0.0.0.0:2348");

// 启动4个进程对外提供服务
$worker->count = 10;


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
    $mysqli = new UseMysqli();
    if(empty($data)) $mysqli->_outjson(0,'no key');
    $sql = "SELECT d.status,d.nd,s.status_name,ss.status_name as master_slave_status,d.netstate, d.zbd FROM devices AS d LEFT JOIN status AS s ON s.id=d.status LEFT JOIN status AS ss ON ss.id=d.zbd WHERE d.devicenum = '".$data."' ORDER BY create_time DESC";
    $rows = $mysqli->use_mysqli_common($sql);
    
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
    Timer::del($connection->timer_id);
};

Worker::runAll();

?>