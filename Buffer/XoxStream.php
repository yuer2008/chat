<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace app\Buffer;
use app\Buffer\XoxProtocol;


/**
 * XoxStream 协议服务端解包和打包
 */
class XoxStream
{
    /**
     * 打包，当向客户端发送数据的时候会自动调用
     * @param string $buffer
     * @return string
     */
    public static function encode(XoxProtocol $protocol)
    {
        $protocol->encode();
        $new = new BigEndianBytesBuffer();
        $new->writeInt($protocol->len);
        $new->writeInt($protocol->cmd);
        $new->writeShort($protocol->flag);
        $new->writeBytes($protocol->body);
        return $new->readAllBytes();
    }
    /**
     * 解包，当接收到的数据字节数等于input返回的值（大于0的值）自动调用
     * 并传递给onMessage回调函数的$data参数
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {
        $new = new XoxProtocol();
        $old = new BigEndianBytesBuffer($buffer);
        $struct = $old->readStruct(
            array(
                "len"=>"int",
                "cmd"=>"int",
                "code"=>"short",
                "body"=>"bytes"
            ));
        $new->body = $struct["body"];
        $new->cmd = $struct["cmd"];
        return $new;
    }
}
