<?php
use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Mysql\Connection;
include __DIR__."/Autoloader.php";
$worker = new Worker("websocket://0.0.0.0:9110");
$redis = new Redis();
$redis->connect("localhost", "6379");
// the user count
$connection_count = 0;
$worker->onConnect = function($connection){
	global $connection_count;
	++$connection_count;
	echo $connection->id . "\n";
};
$worker->onMessage = function($conn, $data){
	echo 'get client msg :' . $data;
	global $db;
	global $redis;
	if(empty($data))return;
	$data = json_decode($data, true);
	$type = $data['type'];
	$d = $data['data'];
	switch($type){
		case 100:	//login
			$name =  $d['name'];
			$pwd = $d['pwd'];
			$db = new Connection('localhost', '3306', 'root', '123456', 'chatroom');
			$user = $db->from('chat_users')->select(['user_name','id'])->where('user_name="'.$name.'"')->row();
			if($user){
				$conn->send(json_encode(['type'=>100,"code"=>1,'data'=>['uid'=>$user['id'],'user_name'=>$name], "msg"=>"login success"]));
				$redis->sadd('chat_online_user', $user['id'].':'.$name);
			}else{
				$conn->send(json_encode(['type'=>100,"code"=>0, "msg"=>"login fail"]));
			}
			break;
		case 301:
			$msg = $d['message'];
			$conn->send(json_encode(["type"=>301,"code"=>1, "data"=>["msg"=>$msg]]));
			foreach($conn->worker->connections as $c){
				if($conn->id != $c->id)
				$c->send(json_encode(["type"=>301,"code"=>1, "data"=>["msg"=>$msg]]));
			}
			break;
		default:
		break;
	}
	
//	print_r(json_encode($data));
	//$conn->send("id:" . $worker::id);
};
$worker->onClose = function($connection){
	global $connection_count;
	$connection_count--;
};
$worker->count=1;
$worker->onWorkerStart = function($worker){
	//global $connection_count;
	//global $redis;
	//定时 每10s一次,online user count
	Timer::add(10, function()use($worker){
		global $connection_count;
		global $redis;
		$ol = $redis->smembers('chat_online_user');
		foreach($worker->connections as $c){
			$c->send(json_encode(["type"=>201,"code"=>1, "data"=>["count"=>$redis->scard('chat_online_user'),'online_user_list'=>$ol]]));
		}
	});
//	$conn->send("id:" . $worker::id);
};
Worker::runAll();
?>
