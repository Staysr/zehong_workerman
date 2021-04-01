<?php
use Workerman\Worker;
use Workerman\lib\Timer;
require_once __DIR__ . '/DataOut.php';
use Workerman\UseMysqli;
require_once __DIR__ . '/Autoloader.php';

define('HEARTBEAT_TIME', 20);
// 创建一个Worker监听2350端口，使用websocket协议通讯
$worker = new Worker("websocket://0.0.0.0:2350");

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

/**
 * 二级用户大屏投放实时数据
 * @param  [type] $connection
 * @param  String $data       websocket 发过来的数据
 * @return json
 */
function senddata($connection,$data)
{
    $mysqli = new UseMysqli();
    if(empty($data)) $mysqli->_outjson(0,'no key');
    $datas = json_decode($data,true);

    // 用户大屏下方4个不同设备的报警
    if($datas['type'] == 'alarm')
    {
        $sql = "SELECT d.dtype,d.devicenum,ud.device_name,s.status_name,FROM_UNIXTIME(d.create_time,'%m-%d %H:%i:%s') as create_time,d.nd FROM devices AS d 
                    LEFT JOIN status AS s ON s.id=d.status
                    LEFT JOIN `$datas[username]_devices` AS ud ON ud.devicenum=d.devicenum
                    WHERE d.status in (4,5,6)  AND d.username='$datas[username]' AND d.dtype IN ($datas[device_type]) 
                    ORDER BY d.create_time DESC";
        $data = $mysqli->use_mysqli_call($sql);
    }
    // 设备实时统计
    elseif($datas['type'] == 'real'){
             // 设备各类型数量统计
             // $sql = "SELECT status, COUNT(id) AS count FROM devices WHERE username='$datas[username]' AND dtype IN ($datas[device_type]) GROUP BY status";
            // $data = $mysqli->use_mysqli($sql);
            $devices = $datas['devices'];
            $realtimeStatistics = [
                ['product', '正常', '报警', '离线']
            ];
            foreach ($devices as $key =>$device) {
                   // 各类型设备数量统计
                    $count = deviceCount( $datas['username'], $device['tid'] );
                    $devices[$key]['value'] = $count;
                    $devices[$key]['name'] = '['.$count.']'.$device['tname'];
                    unset($devices[$key]['tid'],$devices[$key]['tname']);
                     // 设备实时统计
                    $status_categories = deviceTypeOfCount( $datas['username'], $device['tid'] );
                    $alarm_count = 0;
                    $normal_count = 0;
                    $offline_count = 0;
                    foreach ($status_categories as $key => $value) {
                        if($value['status'] == 1){
                            $normal_count +=  $value['status_count'];
                        }elseif($value['status'] == 4 || $value['status'] == 5 || $value['status'] == 6){
                            $alarm_count += $value['status_count'];
                        }elseif($value['status'] == 9){
                            $offline_count += $value['status_count'];
                        }
                    }
                    array_push( $realtimeStatistics, [
                        $device['tname'],$normal_count, $alarm_count,$offline_count
                    ] );
            }
            $devices = array_values($devices);

            $data = compact('devices', 'realtimeStatistics');
    }
    $connection->send(json_encode($data,JSON_UNESCAPED_UNICODE));
}

/**
 * 设备数量
 * @param  string $username 用户名
 * @param  string $type     设备类型
 * @return array use_mysqli_call
 */
function deviceCount($username, $type)
{
    $mysqli = new UseMysqli();
       $sql = "SELECT count(deviceid) as a from $username"."_devices AS ud LEFT JOIN devices AS d ON ud.devicenum = d.devicenum WHERE d.username = '$username' AND  ud.device_type in ($type)";
        $data = $mysqli->use_mysqli_call($sql);
        return $data[0]['a'];
}

/**
 * 每个设备类型不同状态下的设备数量
 * @param  string $username 用户名
 * @param  string $device_types 设备类型
 * @return Array
 */
function deviceTypeOfCount($username, $device_types)
{
    $mysqli = new UseMysqli();
    $sql = "SELECT status,COUNT(id) AS status_count FROM devices WHERE username = '$username' AND dtype in ($device_types) GROUP BY status";
         $data = $mysqli->use_mysqli_call($sql);
    return $data;
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