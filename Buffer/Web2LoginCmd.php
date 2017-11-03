<?php
namespace Workerman\Buffer;

use Workerman\Buffer\XoxProtocol;
use Workerman\XoxServer;

require_once "XoxProtocolDefine.php";
class Web2LoginCmd
{

    //web命令转成login能接受的命令
    public static function ConvertW2L($fromID, $Msg)
    {
        $protocol = new XoxProtocol();
        $writer = new Stream();
        $writer->writeString($fromID);
        switch($Msg->command)
        {
            case "login":
                $account = $Msg->account;
                $password = $Msg->password;
                $sessionkey = $Msg->sessionkey;
                $writer->writeString($account);
                $writer->writeString($password);
                $writer->writeString($sessionkey);
                $protocol->cmd= CLIENT2WEB_LOGIN_REQUEST;
                $protocol->body = $writer->readAllBytes();
                break;
            case "register":
                $writer->writeLong($Msg->webid);
                $writer->writeString($Msg->accountid);
                $protocol->cmd = WEB2LOGIN_REGISTER_ACCOUNT_REQUEST;
                $protocol->body = $writer->readAllBytes();
                break;
            default:
                $protocol=null;

        }
        $writer=null;
        return $protocol;
    }

    public static function ConvertL2W($Cmd, $Msg)
    {
        $reader = new Stream($Msg);
        $ret = array();
        $ret["httpConnection"] = $reader->readString();
        switch($Cmd)
        {
            case WEB2LOGIN_REGISTER_ACCOUNT_REQUEST_ASK:
                $data["command"]="register";
                $data["errCode"] = $reader->readInt();
                if($data["errCode"]>0)break;
                $data["uuid"] = $reader->readLong();
                $data["account"] = $reader->readString();
                $data["imid"] = $reader->readLong();
                $data["imaccount"] = $reader->readString();
                $data["imno"] = $reader->readString();
                //web 更新数据
                //Db::Insert("account", $data["account"], $data);
                break;
            default:
                break;
        }
        $ret["data"]=$data;
        return $ret;
    }

    public static function sendIpPortToClient($Cmd,$Msg){

    }
}