<?php
use Workerman\Mysql\Connection;
//namespace chat;
class User{
	public static function setItem(){

	}
	public static function getName($uid){
		$redis = Chat::get('redis');
		$uname = $redis->hget('chat_username_h',$uid);
		if(empty($uname)){
			$db = new Connection('localhost', '3306', 'root', '123456', 'chatroom');
			$user = $db->from('chat_users')->select(['user_name','id'])->where('id='.$uid)->row();
			$redis->hset('chat_username_h',$uid, $user['user_name']);
			$uname = $redis->hget('chat_username_h',$uid);
		}
		return $uname;
	}
	public static function batchName($ids){
		$data = [];
		if(is_array($ids) && count($ids) >0){
			foreach ($ids as $key => $value) {
				$name = self::getName($value);
				$data[] = ['id'=>$value, 'name'=> $name];
			}
		}
		return $data;
	}
}
?>