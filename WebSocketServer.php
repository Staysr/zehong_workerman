<?php
use Workerman\Worker;
use Workerman\lib\Timer;
require_once __DIR__ . '/Usemysql.php';
use Workerman\Usemysql;
require_once __DIR__ . '/Autoloader.php';

define('HEARTBEAT_TIME', 20);
// 创建一个Worker监听2346端口，使用websocket协议通讯
$worker = new Worker("websocket://0.0.0.0:2352");

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
    $mysql = new Usemysql();
    $result = empty($data) ? $mysql->_outjson(401,'param error') : '';
    $request = json_decode(base64_decode($data), true);
    // 二级用户报警弹窗
    if($request['type'] == 'notify'){
        if(!array_key_exists('username', $request)){
            $result = ['code' => 401, 'msg' => 'param error'];
        }else{
            $result = $mysql->selectArray("
                SELECT a.*,FROM_UNIXTIME(a.alarmstart) AS alarm_time, d.*, w.danwei, s.status_name FROM $request[username]_alarm AS a 
                LEFT JOIN $request[username]_devices AS d ON a.dnum = d.devicenum 
                LEFT JOIN danwei AS w ON w.id = d.danwei_id 
                LEFT JOIN status AS s ON s.id = a.alarmtype
                WHERE a.hasread = 0 LIMIT 100
                ");
        }
    }
    
    $connection->send($mysql->_outjson(201,'OK', $result));
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