<?php
namespace Workerman;
require_once __DIR__ . '/Zhconfig.php';

use Workerman\Zhconfig;

class UseMysqli extends Zhconfig
{

	public function __construct()
	{
		parent::__construct();
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
	    header("Content-type:application/json;chartset=utf-8");
	    $out_data = ['code'=>$code,'msg'=>$msg,'data'=>$data];
	    die( json_encode($out_data) );
	}

	public function use_mysqli($sql='')
	{
		if(!empty($sql))
		{
			$mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
			$mysql->query('set names utf8');
		    $result = $mysql->query($sql);
		    $data = array();
		    while ($rows = mysqli_fetch_assoc($result)) {
		    	$data[$rows['status']] = $rows['count'];
		    }
		    $mysql->close();
		    return $data;
		}
		else
		{
			return null;
		}
	}

	public function use_mysqli_alarm($sql='')
	{
		if(!empty($sql))
		{
			$mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
			$mysql->query('set names utf8');
		    $result = $mysql->query($sql);
		    $data = array();
		    while ($rows = mysqli_fetch_assoc($result)) {
		    	$data[] = [
		    		'devicenum'=>$rows['devicenum'],
		    		'status_name' => $rows['status_name'],
					'create_time' => date('Y-m-d H:i:s',$rows['create_time']),
					'nd' => $rows['nd'],
					'nickname' => $rows['nickname']
		    	];
		    }
		    $mysql->close();
		    return $data;
		}
		else
		{
			return null;
		}
	}

	public function use_mysqli_map($sql='')
	{
		if(!empty($sql))
		{
			$mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
			$mysql->query('set names utf8');
		    $result = $mysql->query($sql);
		    $data = array();
		    while ($rows = mysqli_fetch_assoc($result)) {
		    	$data[] = [
					'devicenum' => $rows['devicenum'],
					'status' => $rows['status'],
					'nd' => $rows['nd'],
					'status_name' => $rows['status_name']
				];
		    }
		    $mysql->close();
		    return $data;
		}
		else
		{
			return null;
		}
	}

	public function use_mysqli_common($sql='')
	{
		if(!empty($sql))
		{
			$mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
			$mysql->query('set names utf8');
		    $result = $mysql->query($sql);
		    $row = mysqli_fetch_row($result);
		    $mysql->close();
		    return $row;
		}
		else
		{
			return null;
		}
	}

	public function insert_database($sql='')
	{
		if(!empty($sql))
		{
			$config = $this->master_database_config;
			$mysql = new \mysqli($config['host'], $config['username'], $config['password'], $config['database']);
			$mysql->query('set names utf8');
			$result = $mysql->query($sql);
		    $mysql->close();
		    return $result;
		}
		else
		{
			return null;
		}
	}

	public function use_mysqli_alarm_data_center($sql='')
	{
		if(!empty($sql))
		{
			$mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
			$mysql->query('set names utf8');
		    $result = $mysql->query($sql);
		    $data = array();
		    while ($rows = mysqli_fetch_assoc($result)) {
		    	$data[] = [
		    		'devicenum'=>$rows['devicenum'],
		    		'status_name' => $rows['status_name'],
					'create_time' => date('Y-m-d H:i:s',$rows['create_time']),
					'nd' => $rows['nd']=='null'?'--':$rows['nd'],
					'nickname' => $rows['nickname']
		    	];
		    }
		    $mysql->close();
		    return $data;
		}
		else
		{
			return null;
		}
	}

	public function use_mysqli_call($sql='')
	{
		if(!empty($sql))
		{
			$mysql = new \mysqli($this->host, $this->username, $this->password, $this->database);
			$mysql->query('set names utf8');
		    $result = $mysql->query($sql);
		    $data = array();
		    while ($rows = mysqli_fetch_assoc($result)) {
		    	$data[] = $rows;
		    }
		    $mysql->close();
		    return $data;
		}
		else
		{
			return null;
		}
	}
}