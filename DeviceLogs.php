<?php
use Workerman\Worker;
use Workerman\lib\Timer;
require_once __DIR__ . '/DataOut.php';
use Workerman\UseMysqli;
require_once __DIR__ . '/Autoloader.php';

define('HEARTBEAT_TIME', 600);
// 创建一个Worker监听2346端口，使用websocket协议通讯
$worker = new Worker("websocket://0.0.0.0:2377");

// 启动1个进程对外提供服务
$worker->count = 1;

$worker->onWorkerStart = function($task)
{
    set_time_limit(6);
    ini_set('max_execution_time',500);
    header("Content-Type: text/plain; charset=utf-8");
    updateDeviceLogs();
    Timer::add(12, function()
    {
        updateDeviceLogs();
    });
};

/**
 * 记录设备状态
 * @return [type] [description]
 */
function updateDeviceLogs()
{
    $mysqli = new UseMysqli();
    $sql = "SELECT devicenum, username, status, pre_status, create_time FROM devices WHERE status = 9 OR pre_status = 9 ORDER BY create_time DESC LIMIT 10000";
    $result = $mysqli->use_mysqli_call($sql);

    foreach ($result as $key => $value) {
        $sql = "SELECT alarmtype,alarmid,alarmstop FROM ".$value['username']."_alarm WHERE dnum='$value[devicenum]' AND alarmtype=9 ORDER BY alarmstart DESC LIMIT 1";
        $row = $mysqli->use_mysqli_call($sql);
        $stop_time = $value['create_time'];
        if($row){
            if($value['status'] == 9 && $value['pre_status'] != 9)
            {
                if($row[0]['alarmstop'] != NULL){
                    $sql = "INSERT INTO ".$value['username']."_alarm (dnum,alarmtype,alarmstart,hasread) VALUES ('$value[devicenum]',$value[status],'$value[create_time]',1)";
                    $mysqli->insert_database($sql);
                }
            }elseif($value['status'] != 9 && $value['pre_status'] == 9){
                // 更新结束时间
                $sql = "UPDATE ".$value['username']."_alarm SET alarmstop=$stop_time WHERE alarmid=".$row[0]['alarmid'];
                $update_row = $mysqli->insert_database($sql);
            }
        }else{
            if($value['status'] == 9 && $value['pre_status'] != 9)
            {
                $sql = "INSERT INTO ".$value['username']."_alarm (dnum,alarmtype,alarmstart,hasread) VALUES ('$value[devicenum]',$value[status],'$value[create_time]',1)";
                $mysqli->insert_database($sql);
            }
        }
    }

}

/**
 * redis 操作类
 */
class MyRedis
{
    private static $handler;

    private static function handler()
    {
        if(!self::$handler){
            self::$handler = new \Redis();
            self::$handler->connect('127.0.0.1',6379);
        }
    }

    /**
     * 获取
     * @param  string $key
     * @return string || object || array
     */
    public static function get($key)
    {
        self::handler();
        $value = self::$handler->get($key);
        $value_unserizlize = @unserialize($value);
        if(is_object($value_unserizlize) || is_array($value_unserizlize)){
            return $value_unserizlize;
        }
        return $value;
    }
    /**
     * 写入redis
     * @param string $key
     * @param object array $value
     */
    public static function set($key,$value)
    {
        self::handler();
        if(is_object($value) || is_array($value)){
            $value = serialize($value);
        }
        return self::$handler->set($key,$value);
    }
    /**
     * 删除redis key
     * @param  string $key
     * @return boolean
     */
    public static function delete($key=0)
    {
        self::handler();
        if($key==0){
            return self::$handler->flushdb();
        }else{
            return self::$handler->del($key);
        }
    }
}
Worker::runAll();

?>