<?php

namespace app\Buffer;


include "XoxProtocolDefine.php";
class App2WebCmd
{
    public static function Route(TcpConnection $connection, XoxProtocol $data)
    {
        XoxServer::LOG_DEBUG("app2web route remoteip ".$connection->getRemoteIp()." cmd ".$data->cmd,__FILE__,__LINE__);
        switch ($data->cmd)
        {
            case CLIENT2WEB_LOGIN_REQUEST:
                $reader = new Stream($data->body);
                $account = $reader->readString();
                $password= $reader->readString();
                $result = Db::Find("account", array("account"=>$account));
                XoxServer::LOG_DEBUG("account find ".var_export($result,true),__FILE__,__LINE__);
                if($result)
                {
                    $writer = new Stream();
                    $login = LoginServer::Better();
                    if($login==false || $login == null)
                    {
                        $writer->writeInt(1000);
                    }else
                    {

                        $writer->writeInt(0);
                        $writer->writeLong($result["uuid"]);
                        $writer->writeLong($result["imid"]);
                        $writer->writeString($login->no);
                        $writer->writeString($login->name);
                        $writer->writeString($login->ip);
                        $writer->writeShort(8001);
                    }
                    $retProtocol = new XoxProtocol();
                    $retProtocol->cmd = CLIENT2WEB_LOGIN_REQUEST_ASK;
                    $retProtocol->body=$writer->readAllBytes();
                    $connection->send($retProtocol);
                }
                break;
            default:
                var_dump($data);
        }

    }
}