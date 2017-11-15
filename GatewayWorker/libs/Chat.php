<?php
use Workerman\Mysql\Connection;
class Chat{
	public static $db;
	public static $app;
	public static function init(){
		
	}
	public static function createObject($name){

	}
	public static function get($name){
		if($name == 'redis'){
			$redis = new Redis();
			$redis->connect("localhost", "6379");
			return $redis;
		}
		if($name == 'db'){
			$db = new Connection('localhost', '3306', 'root', '123456', 'chatroom');
			return $db;
		}
	}
}	
?>
