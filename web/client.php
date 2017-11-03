<?php
$socket = stream_socket_client("tcp://192.168.1.235:9110");
if($socket){
	fwrite($socket, "hello");
	fclose($socket);
}
?>
