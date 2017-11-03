<?php
namespace app\Buffer;
require_once 'XoxProtocolDefine.php';
use app\Buffer\LoginServerInfo;
use app\Buffer\XoxProtocol;
use app\Buffer\Stream;
class LoginServer
{
    public static function LoginClientMsg($msg)
    {
        $writer = new Stream();
        $writer->writeString(json_encode($msg));
        $protocol = new XoxProtocol();
        $protocol->cmd = WEB2LOGIN;
        $protocol->body= $writer->readAllBytes();
        return $protocol;
    }

    public static function ClientLoginMsg($msg){
        $writer = new Stream();
        $writer->writeLong($msg["uuid"]);
        $writer->writeLong($msg['imid']);
        $writer->writeString($msg["sessionkey"]);
        $protocol = new XoxProtocol();
        $protocol->cmd = CLIENT2LOGIN_LOGIN_REQUEST;
        $protocol->body= $writer->readAllBytes();
        return $protocol;
    }

}

