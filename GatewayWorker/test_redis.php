<?php
$redis = new Redis();
$redis->connect("localhost",6379);
$data = $redis->hgetall("chat_username_h");
print_r($data);
?>
