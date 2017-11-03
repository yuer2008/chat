<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 15-5-19
 * Time: 下午1:56
 */

namespace Workerman\Buffer;

use Workerman\Connection\AsyncTcpConnection;
use Workerman\XoxServer;

class Web2LoginTCP extends AsyncTcpConnection
{
    public static $connections = array();
    public function __construct($tcpScheme, $id)
    {
        AsyncTcpConnection::__construct($tcpScheme);
        $this->id = $id;
        Web2LoginTCP::$connections[$id]= $this;
        $count = count(Web2LoginTCP::$connections);
        //XoxServer::LOG_DEBUG("web2login link count{$count}",__FILE__, __LINE__);

    }

    /**
     * 析构函数
     * @return void
     */
    public function __destruct()
    {
        // 统计数据
        parent::__destruct();
        unset(Web2LoginTCP::$connections[$this->id]);
    }

/*    public static function shutdown(){
        foreach(static::$connections as $key=>$value){
            unset(static::$connections[$key]);
        }
    }*/

}