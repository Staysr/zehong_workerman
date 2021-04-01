<?php
use Workerman\Worker;
use Workerman\lib\Timer;
require_once __DIR__ . '/DataOut.php';
use Workerman\UseMysqli;
require_once __DIR__ . '/Autoloader.php';

define('HEARTBEAT_TIME', 20);
// 创建一个Worker监听2346端口，使用websocket协议通讯
$worker = new Worker("websocket://0.0.0.0:2346");

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
    if($data == 1)
    {
        $sql = "SELECT d.devicenum,s.status_name,d.create_time,d.nd,u.nickname FROM devices AS d 
                    LEFT JOIN status AS s ON s.id=d.status
                    LEFT JOIN users AS u ON u.username=d.username
                    WHERE d.status in (4,5,6) 
                    ORDER BY d.create_time DESC";
        $data = $mysqli->use_mysqli_alarm_data_center($sql);
    }
    else
    {
        $sql = "SELECT d.status, COUNT(d.id) AS count FROM devices AS d RIGHT JOIN ".$data."_devices AS ud ON d.devicenum = ud.devicenum WHERE d.username = '".$data."' GROUP BY d.status";
        $rows = $mysqli->use_mysqli($sql);
        $sql = "SELECT d.devicenum,d.nd,u.nickname,s.status_name,d.create_time FROM devices AS d 
            LEFT JOIN status AS s ON s.id=d.status 
            LEFT JOIN users AS u ON u.username=d.username 
            WHERE d.username = '".$data."' AND d.status in (4,5,6) 
            GROUP BY d.id ORDER BY d.create_time DESC";
        $alarm_devices = $mysqli->use_mysqli_alarm($sql);
        $data = array();
        $data['counts'] = $rows;
        $data['devices'] = $alarm_devices;
    }
    $connection->send(json_encode($data,JSON_UNESCAPED_UNICODE));
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