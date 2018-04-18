<?php
use Workerman\Worker;
use Workerman\Lib\Timer;


$worker = new Worker("websocket://".$config['ip'].':'.$config['port']);

// $user = new User();
// echo $user->getName(2);
$redis = Chat::get('redis');
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
			$db = Chat::get('db');
			$user = $db->from('chat_users')->select(['user_name','id'])->where('user_name="'.$name.'"')->row();
			if($user){
				$conn->send(json_encode(['type'=>100,"code"=>1,'data'=>['uid'=>$user['id'],'user_name'=>$name], "msg"=>"login success"]));
				$redis->sadd('chat_online_user', $user['id']);
			}else{
				$conn->send(json_encode(['type'=>100,"code"=>0, "msg"=>"login fail"]));
			}
			break;
		case 101: //logout
			$uid = $d['uid'];
			$n = $redis->srem('chat_online_user', $uid);
			if($n > 0)
			$conn->send(json_encode(['type'=>101,"code"=>1, "msg"=>"loginout success"]));
			break;
		case 301: // send message
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
	//定时 每10s一次,online user count
	Timer::add(10, function()use($worker){
		global $connection_count;
		global $redis;	
		$ol = $redis->smembers('chat_online_user');
		$userInfo = User::batchName($ol);
		foreach($worker->connections as $c){
			$c->send(json_encode(["type"=>201,"code"=>1, "data"=>["count"=>$redis->scard('chat_online_user'),'online_user_list' => $userInfo]]));
		}
	});
//	$conn->send("id:" . $worker::id);
};
// Worker::runAll();
?>
