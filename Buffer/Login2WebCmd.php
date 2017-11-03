<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 15-5-15
 * Time: 下午3:53
 */

namespace Workerman\Buffer;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\XoxStream;
use Workerman\XoxServer;

require_once 'XoxProtocolDefine.php';

// 收到LoginServer的数据，在这个里面解析处理它
class Login2WebCmd {

    public static function Route(TcpConnection $connection, XoxProtocol $protocol)
    {
        switch ($protocol->cmd)
        {
            case LOGIN2WEB_SERVER_START_REQUEST:
                //XoxServer::LOG_DEBUG("success \r\n",__FILE__,__LINE__);
                $body = new BigEndianBytesBuffer($protocol->body);
                $struct = $body->readStruct(array("no"=>"string","name"=>"string","ip"=>"string","port"=>"short"));
                $info = new LoginServerInfo();
                $info->ip = $struct["ip"];
                $info->no = $struct["no"];
                $info->port = $struct["port"];
                $info->name = $struct["name"];
                $info->load = 0;
                LoginServer::AddToDb($info);
                //LoginServer::LinkLoginServer($info);
                /**回发数据给LoginServer**/
                $retBin = new BigEndianBytesBuffer();
                $retBin->writeInt(0);
                $ret = new XoxProtocol();
                $ret->cmd=LOGIN2WEB_SERVER_START_REQUEST_ASK;
                $bodyData = $retBin->readAllBytes();
                $ret->body=$bodyData;
                $connection->send($ret);
                break;
            case LOGIN2WEB_INFO_NOTIFY:
                $reader = new Stream($protocol->body);
                $loginNo = $reader->readString();
                $registerCount = $reader->readInt();
                $loginCount = $reader->readInt();
                $loads = $registerCount+$loginCount;
                LoginServer::UpdateLoads($loginNo,$loads);
                break;
            case WEB2LOGIN:
                LoginServer::$webConections[$connection->id]=$connection;
                $reader = new Stream($protocol->body);
                $json = $reader->readString();
                LoginServer::SendMsg2LoginServer($connection, json_decode($json));
                //LoginServer::SendMsg2LoginServer($connection, $protocol);
                break;
            case LOGIN2WEB_SESSIONKEY_AUTH:
                var_dump($protocol);
                //监听LoginServer 发送过来的消息
                //去数据库中对比
                //eg   模拟从数据库中读取的数据
                $body = new BigEndianBytesBuffer($protocol->body);
                $account = $body->readLong();
                $imid = $body->readLong();
                $sessionkey = $body->readString();

                $exist_model = LoginServer::CompareSessionKey($account);


                if($exist_model['expire']>time()){
                    $result = 1;
                }else if($exist_model['expire']<time()){
                    $result = 2;
                }else{
                    $result = 3;
                }

                if($exist_model['account']==$account&&$exist_model['sessionkey']==$sessionkey){
                    $errorcode = 0;
                }else{
                    $errorcode = 1;
                }
                $return_back = new BigEndianBytesBuffer();
                $return_back->writeInt($errorcode);
                $return_back->writeLong($account);
                $return_back->writeLong($imid);
                $return_back->writeChar($result);
                $back_protocol = new XoxProtocol();
                $back_protocol ->cmd = LOGIN2WEB_SESSIONKEY_AUTH_ASK;
                $back_protocol->body = $return_back->readAllBytes();
                $connection->send($back_protocol);
                break;


            default:
                var_dump($protocol);

        }
    }
}