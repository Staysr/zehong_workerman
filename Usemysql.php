<?php
namespace Workerman;
require_once __DIR__ . '/Zhconfig.php';

use Workerman\Zhconfig;

class Usemysql extends Zhconfig{

    public function __construct(){
        parent::__construct();
    }
    public function select($sql){
        $slave_mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
        $slave_mysql->query("set names utf8");
        $res = $slave_mysql->query($sql);
        $res = $res->fetch_assoc();
        $slave_mysql->close();
        return $res;
    }

    public function selectArray($sql){
        $slave_mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
        $slave_mysql->query("set names utf8");
        $res = $slave_mysql->query($sql);
        $data = array();
        while ($rows = mysqli_fetch_assoc($res)) {
            $data[] = $rows;
        }
        $slave_mysql->close();
        return $data;
    }

    public function update($sql){
        $master_database = $this->master_database_config;
        $master_mysql = new \mysqli(
            $master_database['host'], 
            $master_database['username'], 
            $master_database['password'], 
            $master_database['database']
        );
        $master_mysql->query("set names utf8");
        $res = $master_mysql->query($sql);
        $master_mysql->close();
        return $res;
    }

    // 处理设备请求的数据,不符合的不允许通过
    public function formatData($str){
        $str = preg_replace("/[^a-z,A-Z,0-9,\/,\.,-]/",'', $str);
        $str = rtrim(trim($str),'/');
        $str = str_replace('null', '--', $str);
        preg_match("/^[a-z,A-Z,0-9,-]{6,32}\/(\d{1,2})\/([0-9,--,\.]{1,8})$/", $str, $out);
        if(count($out) > 0){
            return $str;
        }else{
            return false;
        }
    }
    /**
	 * 数组转换json并输出
	 * @param $code int 状态码
	 * @param $msg string 提示信息
	 * @param $data string or array 输出的数据
	 * @return json
	 */
	public function _outjson($code=9999,$msg='',$data='')
	{
	    $out_data = ['code'=>$code,'msg'=>$msg,'data'=>$data];
	    return base64_encode(json_encode($out_data));
	}
}