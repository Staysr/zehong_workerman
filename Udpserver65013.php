<?php
use Workerman\Worker;
require_once __DIR__ . '/Usemysql.php';
use Workerman\Usemysql;
require_once __DIR__ . '/Autoloader.php';
// 创建一个Worker监听2346端口，使用udp协议通讯
$worker = new Worker("udp://0.0.0.0:65013");

// 启动4个进程对外提供服务
$worker->count = 10;


$worker->onMessage = function($connection, $data)
{
    $mysql = new Usemysql();
    // 筛选数据
    $dataFormatted = $mysql->formatData($data);
    if($dataFormatted == false){
        file_put_contents('udp65013.err.log', date('Y-m-d H:i:s', time()).": $data".PHP_EOL, FILE_APPEND);
        return false;
    }
    file_put_contents('udp65013.log', date('Y-m-d H:i:s', time()).": $dataFormatted".PHP_EOL, FILE_APPEND);

    $data = explode("/",$dataFormatted);
    $dnum = substr( $data[0],0 ,20 );
    $status = (int)$data[1];
    $nd = $data[2] != '--' ? (int)$data[2] : '--';

    //获取设备的用户名、状态
    $sql = "select username,status from devices where devicenum='" . $dnum . "'";
    $res = $mysql->select($sql);
    $username = $res['username'];$prestatus = $res['status'];
    $time = time();
    //更新设备总表
    $sql = "update devices set status=$status,create_time=$time,nd='" . $nd . "' where devicenum='" .$dnum . "'";
    $mysql->update($sql);
    //更新设备上次状态
    if($prestatus != $status){
        $sql = "update devices set pre_status=$prestatus where devicenum='" . $dnum . "'";
        $rows = $mysql->update($sql); 
    }
    //判断设备由正常转报警
    if($prestatus == 1 && ($status == 4 || $status == 5 || $status == 6)){
        $sql = "insert " . $username . "_alarm (dnum,alarmtype,alarmnd,alarmstart,hasread) values ('" .$dnum. "',$status,'" .$nd. "',$time,0)";
        $mysql->update($sql);
    //设备由报警转正常
    }elseif(($prestatus == 4 || $prestatus == 5 || $prestatus == 6) && $status == 1){
        //获取最后一次报警的id
        $sql = "select alarmid from " . $username . "_alarm where dnum = '" . $dnum . "' ORDER BY alarmid desc limit 1";
        $res = $mysql->select($sql);
        $alarmid = $res['alarmid'];
        //插入报警停止时间
        $sql = "update " . $username . "_alarm set alarmstop=" .$time. " where alarmid=" .$alarmid;
        $mysql->update($sql);
    }

};

Worker::runAll();

?>