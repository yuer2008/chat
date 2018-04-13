<?php
use  Workerman\Connection\AsyncTcpConnection;
require_once __DIR__ . '/vendor/autoload.php';
$tcp =new  AsyncTcpConnection("websocket://localhost:9110");

$tcp->onConnect = function($tcp){
	$tcp->send("hello");
};
$tcp->onMessage = function($tcp, $data){
	echo $data;
}
?>
